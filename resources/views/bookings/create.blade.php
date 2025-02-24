@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Booking</h2>
    <form method="POST" action="{{ route('bookings.store') }}">
        @csrf
        <div class="mb-3">
            <label>Customer Name</label>
            <input type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Customer Email</label>
            <input type="email" name="customer_email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Booking Date</label>
            <input type="date" name="booking_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Booking Type</label>
            <select id="booking_type" name="booking_type" class="form-control">
                <option value="full_day">Full Day</option>
                <option value="half_day">Half Day</option>
                <option value="custom">Custom</option>
            </select>
        </div>
        <div class="mb-3" id="booking_slot_field" style="display: none;">
            <label>Booking Slot</label>
            <select name="booking_slot" class="form-control">
                <option value="first_half">First Half</option>
                <option value="second_half">Second Half</option>
            </select>
        </div>
        <div class="mb-3" id="time_fields" style="display: none;">
            <label>Booking From</label>
            <input type="time" name="booking_from" class="form-control">
            <label>Booking To</label>
            <input type="time" name="booking_to" class="form-control">
        </div>
        <button class="btn btn-primary">Book Now</button>
    </form>
</div>

<script>
document.getElementById('booking_type').addEventListener('change', function() {
    document.getElementById('booking_slot_field').style.display = (this.value === 'half_day') ? 'block' : 'none';
    document.getElementById('time_fields').style.display = (this.value === 'custom') ? 'block' : 'none';
});
</script>
@endsection
