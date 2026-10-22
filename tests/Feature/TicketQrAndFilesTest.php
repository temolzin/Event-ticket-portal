<?php

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Mail\TicketMail;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

function qrSeller(): User
{
    return User::factory()->create(['role' => UserRole::Seller]);
}

function qrTicket(array $attributes = [], bool $withFiles = true, ?User $seller = null): Ticket
{
    $seller ??= qrSeller();
    $event = Event::factory()->create(['is_active' => true]);

    $ticketType = TicketType::factory()->create([
        'event_id' => $event->id,
        'key' => 'individual',
        'name' => 'Boleto individual',
    ]);

    $ticket = Ticket::factory()->create($attributes + [
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
        'seller_id' => $seller->id,
        'buyer_name' => 'Cliente Con QR',
        'buyer_email' => 'cliente@ejemplo.com',
    ]);

    if ($withFiles) {
        $ticket->storeBuyerPhoto(UploadedFile::fake()->image('foto.jpg'));
        $ticket->storePaymentReceipt(UploadedFile::fake()->image('comprobante.jpg'));
        $ticket->save();
    }

    return $ticket;
}

it('shows the qr code on the ticket detail', function () {
    $seller = qrSeller();
    $ticket = qrTicket(seller: $seller);

    $this->actingAs($seller)
        ->get("/tickets/{$ticket->id}")
        ->assertOk()
        ->assertSee('<svg', false)
        ->assertSee($ticket->code);
});

it('renders the printable ticket with the qr code', function () {
    $seller = qrSeller();
    $ticket = qrTicket(seller: $seller);

    $this->actingAs($seller)
        ->get("/tickets/{$ticket->id}/download")
        ->assertOk()
        ->assertSee('<svg', false)
        ->assertSee($ticket->code)
        ->assertSee($ticket->buyer_name);
});

it('serves the buyer photo only to the seller and the admin', function () {
    Storage::fake('local');

    $seller = qrSeller();
    $ticket = qrTicket(seller: $seller);

    $response = $this->actingAs($seller)->get("/tickets/{$ticket->id}/photo");

    $response->assertOk();

    expect($response->headers->get('Cache-Control'))
        ->toContain('private')
        ->not->toContain('public');

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get("/tickets/{$ticket->id}/photo")
        ->assertOk();
});

it('serves the payment receipt only to the seller and the admin', function () {
    Storage::fake('local');

    $seller = qrSeller();
    $ticket = qrTicket(seller: $seller);

    $response = $this->actingAs($seller)->get("/tickets/{$ticket->id}/receipt");

    $response->assertOk();

    expect($response->headers->get('Cache-Control'))
        ->toContain('private')
        ->not->toContain('public');
});

it('keeps a seller out of the files of a sale that belongs to someone else', function () {
    Storage::fake('local');

    $ticket = qrTicket(seller: qrSeller());

    $this->actingAs(qrSeller())
        ->get("/tickets/{$ticket->id}/photo")
        ->assertForbidden();

    $this->actingAs(qrSeller())
        ->get("/tickets/{$ticket->id}/receipt")
        ->assertForbidden();
});

it('keeps the manager out of the files', function () {
    Storage::fake('local');

    $ticket = qrTicket();

    $this->actingAs(User::factory()->create(['role' => UserRole::Manager]))
        ->get("/tickets/{$ticket->id}/photo")
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => UserRole::Manager]))
        ->get("/tickets/{$ticket->id}/receipt")
        ->assertForbidden();
});

it('answers with a not found when the file is missing', function () {
    $seller = qrSeller();
    $ticket = qrTicket(withFiles: false, seller: $seller);

    $this->actingAs($seller)
        ->get("/tickets/{$ticket->id}/photo")
        ->assertNotFound();
});

it('sends the ticket by email and marks it as sent', function () {
    Mail::fake();

    $seller = qrSeller();
    $ticket = qrTicket(seller: $seller);

    $this->actingAs($seller)
        ->post("/tickets/{$ticket->id}/send")
        ->assertRedirect(route('tickets.show', $ticket));

    Mail::assertSent(TicketMail::class, function (TicketMail $mail) use ($ticket) {
        return $mail->hasTo($ticket->buyer_email)
            && $mail->hasAttachment(
                Attachment::fromData(fn () => $ticket->qrPng(), 'boleto-'.$ticket->code.'.png')
                    ->withMime('image/png')
            );
    });

    expect($ticket->fresh()->status)->toBe(TicketStatus::Sent)
        ->and($ticket->fresh()->sent_at)->not->toBeNull();
});

it('does not let a seller send a ticket that belongs to someone else', function () {
    Mail::fake();

    $ticket = qrTicket();

    $this->actingAs(qrSeller())
        ->post("/tickets/{$ticket->id}/send")
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('keeps the manager out of the send action', function () {
    Mail::fake();

    $ticket = qrTicket();

    $this->actingAs(User::factory()->create(['role' => UserRole::Manager]))
        ->post("/tickets/{$ticket->id}/send")
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('does not let anyone send a cancelled ticket', function () {
    Mail::fake();

    $seller = qrSeller();
    $ticket = qrTicket(seller: $seller);
    $ticket->update(['status' => TicketStatus::Cancelled]);

    $this->actingAs($seller)
        ->post("/tickets/{$ticket->id}/send")
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('does not let anyone send a ticket that was already used', function () {
    Mail::fake();

    $seller = qrSeller();
    $ticket = qrTicket(seller: $seller);
    $ticket->update(['status' => TicketStatus::Used, 'checked_in_at' => now()]);

    $this->actingAs($seller)
        ->post("/tickets/{$ticket->id}/send")
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('builds the mail with the buyer data and the event', function () {
    $ticket = qrTicket();

    $rendered = (new TicketMail($ticket))->render();

    expect($rendered)->toContain($ticket->buyer_name)
        ->and($rendered)->toContain($ticket->event->name)
        ->and($rendered)->toContain('Boleto digital')
        ->and($rendered)->toContain($ticket->code)
        ->and($rendered)->toContain('cid:'.TicketMail::QR_CID)
        ->and($rendered)->not->toContain('<svg');
});

it('embeds the qr code in the mail with a content id', function () {
    config(['mail.default' => 'array']);

    $ticket = qrTicket();

    Mail::to('cliente@ejemplo.com')->send(new TicketMail($ticket));

    $email = Mail::mailer('array')->getSymfonyTransport()->messages()[0]->getOriginalMessage();

    $inline = collect($email->getAttachments())->first(
        fn ($part) => $part->getContentId() === TicketMail::QR_CID
    );

    expect($inline)->not->toBeNull()
        ->and($inline->getContentType())->toBe('image/png')
        ->and($inline->getDisposition())->toBe('inline')
        ->and($email->getHtmlBody())->toContain('cid:'.TicketMail::QR_CID)
        ->and($email->toString())->toContain('Content-ID: <'.TicketMail::QR_CID.'>');
});

it('generates a png qr code that can be scanned', function () {
    $ticket = qrTicket();

    $png = $ticket->qrPng();

    $info = getimagesizefromstring($png);

    expect($info['mime'])->toBe('image/png')
        ->and($info[0])->toBeGreaterThan(100)
        ->and($info[1])->toBeGreaterThan(100);
});

it('lets the buyer photo download as a file for the badge', function () {
    $seller = qrSeller();
    $ticket = qrTicket(seller: $seller);

    $response = $this->actingAs($seller)->get("/tickets/{$ticket->id}/photo/download");

    $response->assertOk();

    expect($response->headers->get('Content-Disposition'))
        ->toContain('attachment')
        ->toContain('foto-'.$ticket->code)
        ->and($response->headers->get('Cache-Control'))->toContain('private');
});

it('keeps the buyer photo visible inline when it is opened', function () {
    $seller = qrSeller();
    $ticket = qrTicket(seller: $seller);

    $response = $this->actingAs($seller)->get("/tickets/{$ticket->id}/photo");

    $response->assertOk();

    expect($response->headers->get('Content-Disposition'))->toContain('inline');
});
