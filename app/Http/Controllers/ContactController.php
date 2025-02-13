<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function index()
    {
    }

    public function store(StoreContactRequest $request)
    {
        $data = $request->validated();
        $contact = Contact::create($data);
        return new JsonResponse(['message' => 'Message received. We’ll be in touch soon. Thanks for reaching out!', 'reference' => $contact->reference], 200);
    }
}
