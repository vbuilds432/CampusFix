<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
checkRole('Student');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInputs($_POST['title']);
    $description = sanitizeInputs($_POST['description']);
    $category_id = $_POST['category_id'];
    $other_category = sanitizeInputs($_POST['other_category'] ?? null);
    $campus_id = $_POST['campus_id'];
    
    // Accept raw string values for building and spot due to Area decoupling update
    $building_name = sanitizeInputs($_POST['building_name']);
    if($building_name === 'Other') {
        $building_name = sanitizeInputs($_POST['other_building']);
    }
    
    $spot_name = sanitizeInputs($_POST['spot_name']);
    if($spot_name === 'Other') {
        $spot_name = sanitizeInputs($_POST['other_spot']);
    }
    
    $exact_location = sanitizeInputs($_POST['exact_location']);
    $priority = $_POST['priority'];
    $user_id = $_SESSION['user_id'];
    
    if(empty($building_name) || empty($spot_name)) {
        $error = "Building and Spot details are required.";
    } else {
        // Handle file upload
        $file_path = null;
        if(isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] == UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $file_name = $_FILES['proof_file']['name'];
            $tmp_name = $_FILES['proof_file']['tmp_name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if(in_array($ext, $allowed) && $_FILES['proof_file']['size'] <= 2 * 1024 * 1024) {
                $new_name = uniqid('proof_', true) . '.' . $ext;
                $upload_dir = 'uploads/proofs/';
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                if(move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                    $file_path = $upload_dir . $new_name;
                }
            } else {
                $error = "Invalid file type or size.";
            }
        }
        
        if(!$error) {
            // Generate sequential semantic ID
            $prefix = "CMP_";
            $stmtId = $pdo->prepare("SELECT complaint_id_str FROM complaints WHERE complaint_id_str LIKE ? ORDER BY id DESC LIMIT 1");
            $stmtId->execute([$prefix . "%"]);
            $last_id_row = $stmtId->fetch();

            if ($last_id_row) {
                // Extract numeric part from the end (e.g., from CMP_005 extract 5)
                $last_id_str = $last_id_row['complaint_id_str'];
                $parts = explode('_', $last_id_str);
                $last_num = (int)end($parts);
                $new_num = $last_num + 1;
            } else {
                $new_num = 1;
            }
            $c_id_str = $prefix . str_pad($new_num, 3, '0', STR_PAD_LEFT);
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO complaints (complaint_id_str, user_id, title, description, category_id, other_category, campus_id, building_name, spot_name, exact_location, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Submitted')");
                $stmt->execute([$c_id_str, $user_id, $title, $description, $category_id, $other_category, $campus_id, $building_name, $spot_name, $exact_location, $priority]);
                $complaint_id = $pdo->lastInsertId();
                
                // insert attach
                if($file_path) {
                    $stmt = $pdo->prepare("INSERT INTO attachments (complaint_id, file_name, file_path, file_type, uploaded_by) VALUES (?, ?, ?, 'Complaint Proof', ?)");
                    $stmt->execute([$complaint_id, $file_name, $file_path, $user_id]);
                }
                
                // insert history
                $stmt = $pdo->prepare("INSERT INTO complaint_history (complaint_id, status, remarks, updated_by) VALUES (?, 'Submitted', 'Initial Submission', ?)");
                $stmt->execute([$complaint_id, $user_id]);
                
                $pdo->commit();
                $success = "Complaint registered successfully! ID: $c_id_str";
                

            } catch(Exception $e) {
                $pdo->rollBack();
                $error = "Failed to register complaint: " . $e->getMessage();
            }
        }
    }
}

// Fetch basic data for dropdowns
$categories = $pdo->query("SELECT id, name FROM complaint_categories WHERE is_active = 1")->fetchAll();
$campuses = $pdo->query("SELECT id, name FROM campus")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Register New Complaint</h2>
            <a href="<?= BASE_URL ?>/dashboard_student.php" class="btn btn-outline-secondary">Go Back</a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Complaint Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category_id" id="category" class="form-select" required>
                            <option value="">Select Category...</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="other_category_container" style="display:none;">
                        <label>Specify Other Category</label>
                        <input type="text" name="other_category" id="other_category_input" class="form-control">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Campus</label>
                            <select name="campus_id" id="campus_select" class="form-select" required>
                                <option value="">Select Campus...</option>
                                <?php foreach($campuses as $camp): ?>
                                    <option value="<?= $camp['id'] ?>"><?= $camp['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Building</label>
                            <select name="building_id_temp" id="building_select" class="form-select" required>
                                <option value="">Select Campus First...</option>
                            </select>
                            <input type="hidden" name="building_name" id="hidden_building_name">
                            <input type="text" name="other_building" id="other_building_input" class="form-control mt-2" placeholder="Enter Building Name" style="display:none;">
                        </div>
                        <div class="col-md-4">
                            <label>Spot</label>
                            <select name="spot_id_temp" id="spot_select" class="form-select" required>
                                <option value="">Select Building First...</option>
                            </select>
                            <input type="hidden" name="spot_name" id="hidden_spot_name">
                            <!-- The placeholder will be updated dynamically via JS -->
                            <textarea name="other_spot" id="other_spot_input" class="form-control mt-2" rows="2" placeholder="Enter Custom Spot" style="display:none;"></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Exact Location / Detail</label>
                            <input type="text" name="exact_location" class="form-control" required placeholder="e.g. Near window, etc">
                        </div>
                        <div class="col-md-6">
                            <label>Priority</label>
                            <select name="priority" class="form-select" required>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label>Upload Proof Image/PDF (Max 2MB)</label>
                        <input type="file" name="proof_file" id="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Complaint</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    // Other Category toggle
    $('#category').change(function() {
        if ($('#category option:selected').text().trim() === 'Other') {
            $('#other_category_container').show();
            $('#other_category_input').prop('required', true);
        } else {
            $('#other_category_container').hide();
            $('#other_category_input').prop('required', false);
        }
    });

    // Cascade Campus -> Building
    $('#campus_select').change(function() {
        var campus_id = $(this).val();
        var buildingSelect = $('#building_select');
        var spotSelect = $('#spot_select');
        
        buildingSelect.html('<option value="">Loading...</option>');
        spotSelect.html('<option value="">Select Building First...</option>');
        $('#hidden_building_name').val('');
        $('#hidden_spot_name').val('');
        $('#other_building_input').hide();
        $('#other_spot_input').hide();

        if(campus_id) {
            $.ajax({
                url: 'api/get_buildings.php',
                type: 'GET',
                data: { campus_id: campus_id },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        var options = '<option value="">Select Building...</option>';
                        if(response.data.length > 0) {
                            $.each(response.data, function(index, building) {
                                options += '<option value="' + building.id + '" data-name="' + building.name + '">' + building.name + '</option>';
                            });
                        }
                        options += '<option value="Other" data-name="Other">Other</option>';
                        buildingSelect.html(options);
                    } else {
                        buildingSelect.html('<option value="">Error</option>');
                    }
                }
            });
        } else {
            buildingSelect.html('<option value="">Select Campus First...</option>');
        }
    });

    // Cascade Building -> Spot
    $('#building_select').change(function() {
        var building_id = $(this).val();
        var building_name = $(this).find(':selected').data('name') || '';
        $('#hidden_building_name').val(building_name);
        
        var spotSelect = $('#spot_select');
        spotSelect.html('<option value="">Loading...</option>');
        $('#hidden_spot_name').val('');
        $('#other_spot_input').hide();

        if (building_name === 'Other') {
            $('#other_building_input').show().prop('required', true);
            spotSelect.html('<option value="Other" data-name="Other">Other</option>');
            $('#hidden_spot_name').val('Other');
            $('#other_spot_input').show().prop('required', true);
        } else {
            $('#other_building_input').hide().prop('required', false);
            
            if(building_id) {
                $.ajax({
                    url: 'api/get_spots.php',
                    type: 'GET',
                    data: { building_id: building_id },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            var options = '<option value="">Select Spot...</option>';
                            if(response.data.length > 0) {
                                $.each(response.data, function(index, spot) {
                                    options += '<option value="' + spot.id + '" data-name="' + spot.name + '">' + spot.name + '</option>';
                                });
                            }
                            options += '<option value="Other" data-name="Other">Other</option>';
                            spotSelect.html(options);
                        } else {
                            spotSelect.html('<option value="">Error</option>');
                        }
                    }
                });
            } else {
                spotSelect.html('<option value="">Select Building First...</option>');
            }
        }
    });

    $('#spot_select').change(function() {
        var spot_name = $(this).find(':selected').data('name') || '';
        $('#hidden_spot_name').val(spot_name);
        
        if (spot_name === 'Other') {
            $('#other_spot_input').show().prop('required', true);
        } else {
            $('#other_spot_input').hide().prop('required', false);
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
