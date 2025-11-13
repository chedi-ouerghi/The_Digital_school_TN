<?php

namespace App\Http\Controllers;

use App\Models\AccountRequest;
use App\Models\User;
use App\Models\Notification;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\NewAccountRequestMail;
use App\Mail\TempPasswordMail;

class AccountRequestController extends Controller
{
    /**
     * Un visiteur soumet une demande d’ouverture de compte.
     */
    public function requestAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:account_requests,email',
        ]);

        $accountRequest = AccountRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => 'PENDING',
        ]);

        $admins = User::where('role', 'ADMIN')->get();

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new NewAccountRequestMail($accountRequest));

                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Nouvelle demande de compte',
                    'message' => "Demande soumise par {$accountRequest->name} ({$accountRequest->email})",
                    'type' => Notification::TYPE_ACCOUNT_REQUEST ?? 'account_request',
                ]);
            } catch (\Throwable $ex) {
                \Log::warning("Notification/mail échoué pour admin {$admin->id}: " . $ex->getMessage());
            }
        }

        return response()->json([
            'message' => 'Votre demande a été soumise avec succès.',
        ]);
    }

}
