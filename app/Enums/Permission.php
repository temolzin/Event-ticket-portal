<?php

namespace App\Enums;

enum Permission: string
{
    case ViewDashboard = 'view_dashboard';
    case ManageEvents = 'manage_events';
    case ManageTicketTypes = 'manage_ticket_types';
    case ViewTickets = 'view_tickets';
    case CreateTicket = 'create_ticket';
    case UpdateOwnTicket = 'update_own_ticket';
    case CancelTicket = 'cancel_ticket';
    case ViewCancelledTickets = 'view_cancelled_tickets';
    case ManageUsers = 'manage_users';
    case ManageBadges = 'manage_badges';
    case PerformCheckIn = 'perform_check_in';
    case ViewScanLog = 'view_scan_log';
    case ViewProfile = 'view_profile';
    case Search = 'search';
}
