<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Configuration;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    protected TwoFactorService $twofa;

    public function __construct(TwoFactorService $twofa)
    {
        $this->twofa = $twofa;
    }

    /**
     * AJAX: Generate a new 2FA secret + QR URI.
     * Does NOT enable 2FA yet — user must verify first.
     */
    public function setup(Request $request, $token)
    {
        $currentAdmin = $request->attributes->get('admin');
        $currentAdminId = $currentAdmin->id;
        $admin = Admin::findByTokenOrFail($token);

        // Only allow self-setup or administrator
        if ($admin->id !== $currentAdminId && !$currentAdmin->isAdministrator()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Generate secret
        $secret = $this->twofa->generateSecret();

        // Store secret (encrypted) but keep 2FA disabled until verified
        $admin->twofa_secret = $secret;
        $admin->twofa_enabled = false;
        $admin->save();

        // Build QR URI
        $portalName = Configuration::get('portal_name', 'Admin Portal');
        $label = $admin->username;
        $uri = $this->twofa->getOtpAuthUri($secret, $label, $portalName);

        return response()->json([
            'success' => true,
            'qr_uri'  => $uri,
            'message'  => 'Scan the QR code with your authenticator app, then enter the 6-digit code to activate.',
        ]);
    }

    /**
     * AJAX: Verify OTP to activate 2FA.
     */
    public function verify(Request $request, $token)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $currentAdmin = $request->attributes->get('admin');
        $currentAdminId = $currentAdmin->id;
        $admin = Admin::findByTokenOrFail($token);

        if ($admin->id !== $currentAdminId && !$currentAdmin->isAdministrator()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if (!$admin->twofa_secret) {
            return response()->json(['success' => false, 'message' => 'No 2FA secret found. Please run setup first.']);
        }

        $code = $request->input('code');

        if ($this->twofa->verifyCode($admin->twofa_secret, $code)) {
            $admin->twofa_enabled = true;
            $admin->save();
            return response()->json(['success' => true, 'message' => '2FA activated successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid code. Please check your authenticator app and try again.']);
    }

    /**
     * AJAX: Disable 2FA for an admin.
     */
    public function disable(Request $request, $token)
    {
        $currentAdmin = $request->attributes->get('admin');
        $currentAdminId = $currentAdmin->id;
        $admin = Admin::findByTokenOrFail($token);

        if ($admin->id !== $currentAdminId && !$currentAdmin->isAdministrator()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $admin->twofa_secret = null;
        $admin->twofa_enabled = false;
        $admin->save();

        return response()->json(['success' => true, 'message' => '2FA disabled successfully.']);
    }
}
