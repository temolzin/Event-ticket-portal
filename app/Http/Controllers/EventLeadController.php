<?php

namespace App\Http\Controllers;

use App\Models\EventLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventLeadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{10}$/'],
            'ticket_type' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Por favor verifica los datos del formulario.',
                'errors' => $validator->errors(),
            ], 422);
        }

        EventLead::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registro enviado correctamente.',
        ]);
    }
}
