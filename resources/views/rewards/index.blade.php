@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1>Rewards Program</h1>
            <p class="lead">Earn points through your participation and redeem them for exclusive benefits</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="card bg-primary text-white">
                <div class="card-body text-center p-3">
                    <h5 class="mb-0">Your Points</h5>
                    <h2 class="display-4 mb-0">{{ $totalPoints }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">How to Earn Points</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-center">
                                <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px">
                                    <i class="fas fa-file-alt fa-2x text-primary"></i>
                                </div>
                                <h5>Complete Applications</h5>
                                <p class="text-muted small">Earn points by successfully completing the application process</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="text-center">
                                <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px">
                                    <i class="fas fa-plane-departure fa-2x text-primary"></i>
                                </div>
                                <h5>Participate in Programs</h5>
                                <p class="text-muted small">Earn more points as you participate in intercultural programs</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px">
                                    <i class="fas fa-users fa-2x text-primary"></i>
                                </div>
                                <h5>Refer Friends</h5>
                                <p class="text-muted small">Earn points when your friends sign up using your referral code</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="mb-4">Available Rewards</h2>
    
    @if($rewards->isEmpty())
        <div class="alert alert-info">
            <p>No rewards available at the moment. Please check back later.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
            @foreach($rewards as $reward)
                <div class="col">
                    <div class="card h-100 shadow-sm {{ $totalPoints >= $reward->cost ? '' : 'border-danger' }}">
                        @if($reward->is_featured)
                            <div class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">Featured</div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $reward->name }}</h5>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-2 badge bg-primary">
                                    <i class="fas fa-star"></i> {{ number_format($reward->cost) }} points
                                </div>
                                @if(!$reward->is_active)
                                    <div class="badge bg-secondary">Currently Unavailable</div>
                                @endif
                            </div>
                            <p class="card-text">{{ $reward->description }}</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 d-flex justify-content-between align-items-center">
                            @if($reward->is_active)
                                @if($totalPoints >= $reward->cost)
                                    <button type="button" class="btn btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#redeemModal{{ $reward->id }}">
                                        <i class="fas fa-gift me-1"></i> Redeem Reward
                                    </button>
                                @else
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-lock me-1"></i> Need {{ number_format($reward->cost - $totalPoints) }} more points
                                    </button>
                                @endif
                            @else
                                <button class="btn btn-outline-secondary" disabled>
                                    <i class="fas fa-clock me-1"></i> Currently Unavailable
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Redeem Modal -->
                <div class="modal fade" id="redeemModal{{ $reward->id }}" tabindex="-1" aria-labelledby="redeemModalLabel{{ $reward->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="redeemModalLabel{{ $reward->id }}">Redeem: {{ $reward->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>You are about to redeem this reward for <strong>{{ number_format($reward->cost) }} points</strong>.</p>
                                <p>Your current balance: <strong>{{ number_format($totalPoints) }} points</strong></p>
                                <p>After this redemption, you will have <strong>{{ number_format($totalPoints - $reward->cost) }} points</strong> remaining.</p>
                                
                                <form action="{{ route('redemptions.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="reward_id" value="{{ $reward->id }}">
                                    
                                    <div class="mb-3">
                                        <label for="notes{{ $reward->id }}" class="form-label">Additional Notes (Optional)</label>
                                        <textarea class="form-control" id="notes{{ $reward->id }}" name="notes" rows="3" placeholder="Any specific details or preferences..."></textarea>
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Confirm Redemption</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h2 class="mb-4">Your Redemption History</h2>
    @if($redemptions->isEmpty())
        <div class="alert alert-info">
            <p>You haven't redeemed any rewards yet.</p>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reward</th>
                                <th>Points Used</th>
                                <th>Requested On</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($redemptions as $redemption)
                                <tr>
                                    <td>{{ $redemption->reward->name }}</td>
                                    <td>{{ number_format($redemption->reward->cost) }}</td>
                                    <td>{{ $redemption->requested_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($redemption->status == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($redemption->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($redemption->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td>{{ $redemption->notes ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            {{ $redemptions->links() }}
        </div>
    @endif
</div>
@endsection