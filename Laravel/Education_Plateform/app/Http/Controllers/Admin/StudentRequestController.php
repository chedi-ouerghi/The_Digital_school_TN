<?php

namespace App\Http\Controllers\Admin;

use App\Models\StudentRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StudentRequestController extends Controller
{
    // Afficher toutes les demandes des étudiants pour rejoindre un cours
    public function index()
    {
        $requests = StudentRequest::all();
        return view('admin.student_requests.index', compact('requests'));
    }

    // Accepter une demande d'étudiant pour rejoindre un cours
    public function accept($id)
    {
        $request = StudentRequest::findOrFail($id);
        $request->update(['status' => 'Accepted']);
        return redirect()->back()->with('success', 'Student request accepted successfully.');
    }

    // Refuser une demande d'étudiant pour rejoindre un cours
    public function reject($id)
    {
        $request = StudentRequest::findOrFail($id);
        $request->update(['status' => 'Rejected']);
        return redirect()->back()->with('success', 'Student request rejected successfully.');
    }
}
