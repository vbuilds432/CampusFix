<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2 class="mb-4 text-center">Track Your Complaint</h2>
        
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form id="trackForm" class="d-flex">
                    <input type="text" id="c_id" name="c_id" class="form-control me-2" placeholder="Enter Complaint ID (e.g. COMP-2026-0001)" required>
                    <button type="submit" class="btn btn-primary px-4">Track</button>
                </form>
            </div>
        </div>
        
        <div id="errorMessage" class="alert alert-danger" style="display:none;"></div>
        
        <div id="complaintDetails" style="display:none;">
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Complaint Status Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-0">
                        <tr><th style="width:30%">Complaint ID</th><td><strong id="d_cid"></strong></td></tr>
                        <tr><th>Title</th><td id="d_title"></td></tr>
                        <tr><th>Current Status</th><td><span class="badge bg-secondary" id="d_status"></span></td></tr>
                        <tr><th>Priority</th><td><span class="badge" id="d_priority"></span></td></tr>
                        <tr><th>Submitted On</th><td id="d_created"></td></tr>
                        <tr><th>Last Updated</th><td id="d_updated"></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#trackForm').submit(function(e) {
        e.preventDefault();
        var cid = $('#c_id').val().trim();
        
        if (cid === '') return;
        
        $('#errorMessage').hide();
        $('#complaintDetails').hide();
        
        $.ajax({
            url: 'api/track_complaint.php',
            type: 'GET',
            data: { cid: cid },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    let d = response.data;
                    $('#d_cid').text(d.complaint_id);
                    $('#d_title').text(d.title);
                    $('#d_status').text(d.current_status);
                    
                    let pClass = 'bg-info';
                    if (d.priority === 'High') pClass = 'bg-danger';
                    else if (d.priority === 'Medium') pClass = 'bg-warning text-dark';
                    
                    $('#d_priority').removeClass('bg-danger bg-warning text-dark bg-info').addClass(pClass).text(d.priority);
                    $('#d_created').text(d.created_at);
                    $('#d_updated').text(d.last_updated);
                    
                    $('#complaintDetails').fadeIn();
                } else {
                    $('#errorMessage').text(response.message || 'Complaint not found').show();
                }
            },
            error: function() {
                $('#errorMessage').text('Failed to connect to tracking service. Please try again.').show();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
