<?php

namespace App\Http\Controllers\Intranet;

use App\Events\IntranetDataChanged;
use App\Http\Controllers\Controller;
use App\Models\Intranet\Message;
use App\Models\Intranet\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::with(['sender', 'recipient'])->orderBy('created_at', 'desc')->paginate(20);

        return view('intranet.messages.index', compact('messages'));
    }

    public function create()
    {
        $students = Student::orderBy('last_name')->get();

        return view('intranet.messages.create', compact('students'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sender_id' => 'required|exists:intranet_students,id',
            'recipient_id' => 'required|exists:intranet_students,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_read' => 'nullable|boolean',
        ]);

        $data['id'] = Str::uuid()->toString();
        $data['is_read'] = $request->boolean('is_read');

        $message = Message::create($data);

        event(new IntranetDataChanged('message', 'create', $message->toArray()));

        return Redirect::route('intranet.messages.index')->with('success', 'Message créé.');
    }

    public function show(string $id)
    {
        $message = Message::with(['sender', 'recipient'])->findOrFail($id);

        return view('intranet.messages.show', compact('message'));
    }

    public function edit(string $id)
    {
        $message = Message::findOrFail($id);
        $students = Student::orderBy('last_name')->get();

        return view('intranet.messages.edit', compact('message', 'students'));
    }

    public function update(Request $request, string $id)
    {
        $message = Message::findOrFail($id);

        $data = $request->validate([
            'sender_id' => 'required|exists:intranet_students,id',
            'recipient_id' => 'required|exists:intranet_students,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_read' => 'nullable|boolean',
        ]);

        $data['is_read'] = $request->boolean('is_read');

        $message->update($data);

        event(new IntranetDataChanged('message', 'update', $message->toArray()));

        return Redirect::route('intranet.messages.index')->with('success', 'Message mis à jour.');
    }

    public function destroy(string $id)
    {
        $message = Message::findOrFail($id);

        $message->delete();

        event(new IntranetDataChanged('message', 'delete', ['id' => $id]));

        return Redirect::route('intranet.messages.index')->with('success', 'Message supprimé.');
    }
}
