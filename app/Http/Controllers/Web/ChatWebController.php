<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;

class ChatWebController extends Controller {
    public function index() {
        $messages = Chat::with('user')->orderBy('created_at', 'asc')->get();
        return view('admin.chat.chat', compact('messages'));
    }
}