<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Justification;
use App\Models\Absence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
   


class JustificationController extends Controller
{
    /**
     * Display a listing of justifications with filters
     */
    public function index(Request $request)
    {
        $query = Justification::with(['student.user', 'absence.session.module']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by date submitted
        if ($request->filled('date_from')) {
            $query->where('submitted_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('submitted_at', '<=', $request->date_to);
        }
        
        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        $justifications = $query->latest()->paginate(20);
        
        return view('admin.justifications.index', compact('justifications'));
    }

    /**
     * Display the specified justification
     */
    public function show(Justification $justification)
    {
        $justification->load(['student.user', 'absence.session.module', 'admin']);
        return view('admin.justifications.show', compact('justification'));
    }

    /**
     * Download justification file
     */
    public function download(Justification $justification)
    {
        if (!Storage::disk('public')->exists($justification->file_path)) {
            abort(404, 'Fichier non trouvé.');
        }
        
        return response()->download(Storage::disk('public')->path($justification->file_path), $justification->file_name);
    }

    /**
     * Validate a justification (approve/reject/request more info)
     */
    public function validateJustification(Request $request, Justification $justification)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject,need_info',
            'comments' => 'required_if:action,reject,need_info|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        switch ($request->action) {
            case 'approve':
                return $this->approveJustification($justification, $request->comments);
                
            case 'reject':
                return $this->rejectJustification($justification, $request->comments);
                
            case 'need_info':
                return $this->requestMoreInfo($justification, $request->comments);
        }
    }

    /**
     * Approve a justification
     */
    private function approveJustification(Justification $justification, $comments = null)
    {
        // Update justification status
        $justification->update([
            'status' => Justification::STATUS_APPROVED,
           'validated_by' => Auth::id(),
            'validation_date' => now(),
            'comments' => $comments,
        ]);
        
        // Update absence status
        if ($justification->absence) {
            $justification->absence->update([
                'status' => Absence::STATUS_JUSTIFIED,
                'justification_id' => $justification->id,
            ]);
        }
        
        // Create notification for student
        $this->createNotification(
            $justification->student->user_id,
            'Justification approuvée',
            'Votre certificat médical a été approuvé.',
            'success',
            $justification
        );
        
        return back()->with('success', 'Justification approuvée avec succès.');
    }

    /**
     * Reject a justification
     */
    private function rejectJustification(Justification $justification, $rejectionReason)
    {
        // Update justification status
        $justification->update([
            'status' => Justification::STATUS_REJECTED,
             'validated_by' => Auth::id(),
            'validation_date' => now(),
            'rejection_reason' => $rejectionReason,
        ]);
        
        // Update absence status (back to unjustified)
        if ($justification->absence) {
            $justification->absence->update([
                'status' => Absence::STATUS_UNJUSTIFIED,
                'justification_id' => null,
            ]);
        }
        
        // Create notification for student
        $this->createNotification(
            $justification->student->user_id,
            'Justification rejetée',
            'Votre certificat médical a été rejeté. Raison: ' . $rejectionReason,
            'error',
            $justification
        );
        
        return back()->with('success', 'Justification rejetée.');
    }

    /**
     * Request more information for a justification
     */
    private function requestMoreInfo(Justification $justification, $comments)
    {
        // Update justification status
        $justification->update([
            'status' => Justification::STATUS_NEEDS_INFO,
            'comments' => $comments,
        ]);
        
        // Create notification for student
        $this->createNotification(
            $justification->student->user_id,
            'Information supplémentaire requise',
            'Des informations supplémentaires sont nécessaires pour votre justification. ' . $comments,
            'warning',
            $justification
        );
        
        return back()->with('success', 'Demande d\'information supplémentaire envoyée.');
    }

    /**
     * Create notification for student
     */
    private function createNotification($userId, $title, $message, $type, $justification)
    {
        \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'related_model' => 'Justification',
            'related_id' => $justification->id,
            'action_url' => route('student.justifications.show', $justification),
        ]);
    }

    /**
     * Bulk validate justifications
     */
    public function bulkValidate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'justification_ids' => 'required|array',
            'justification_ids.*' => 'exists:justifications,id',
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $count = 0;
        
        foreach ($request->justification_ids as $id) {
            $justification = Justification::find($id);
            
            if ($request->action === 'approve') {
                $this->approveJustification($justification, $request->comments);
            } else {
                $this->rejectJustification($justification, $request->comments ?? 'Rejeté en masse');
            }
            
            $count++;
        }

        return back()->with('success', "$count justifications traitées avec succès.");
    }
}