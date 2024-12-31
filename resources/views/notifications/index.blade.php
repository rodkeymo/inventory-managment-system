@extends('layouts.tabler')

@section('content')
<div class="container">
    
<h2 class="m-4">Stock Notifications</h2>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($notifications->isEmpty())
        <p>No notifications available.</p>
    @else
        <ul class="list-group">
            @foreach($notifications as $notification)
                <li class="list-group-item m-2 {{ $notification->read ? 'bg-green' : '' }}">
                    <strong>{{ $notification->product_name }}</strong> is low in stock. 
                    Current Quantity: {{ $notification->current_quantity }}, 
                    Alert Threshold: {{ $notification->alert_threshold }}
                    <br class="mt-2">{{$notification->created_at->format('Y-m-d') }}
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="float-end">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">
                            Mark as Read
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
