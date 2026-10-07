@extends('layouts.education')

@section('title', $message->subject)

@section('content')
    <p class="muted"><a href="{{ route('education.messages.index') }}">← Messages</a></p>
    <h1>{{ $message->subject }}</h1>
    <p class="subtitle">From {{ $message->sender->name }} to {{ $message->recipient->name }} · {{ $message->created_at->format('d M Y H:i') }}</p>
    <div class="card" style="white-space:pre-wrap;">{{ $message->body }}</div>
@endsection
