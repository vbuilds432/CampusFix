<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
checkRole('Admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add Campus
    if(isset($_POST['add_campus'])) {
        $name = sanitizeInputs($_POST['campus_name']);
        
        if (empty(trim($name))) {
            $error = "Campus name cannot be empty.";
        } else {
            $stmt_check = $pdo->prepare("SELECT id FROM campus WHERE name = ?");
            $stmt_check->execute([$name]);
            if($stmt_check->fetch()) {
                $error = "Campus already exists.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO campus (name, status) VALUES (?)");
                if($stmt->execute([$name])) {
                    $success = "Campus added successfully.";
                } else {
                    $error = "Failed to add campus.";
                }
            }
        }
    }
    
    // Add Building
    elseif(isset($_POST['add_building'])) {
        $campus_id = intval($_POST['campus_id']);
        $name = sanitizeInputs($_POST['building_name']);
        
        $stmt_check = $pdo->prepare("SELECT id FROM building WHERE campus_id = ? AND name = ?");
        $stmt_check->execute([$campus_id, $name]);
        if($stmt_check->fetch()) {
            $error = "Building already exists in this campus.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO building (campus_id, name, status) VALUES (?, ?)");
            if($stmt->execute([$campus_id, $name])) {
                $success = "Building added successfully.";
            } else {
                $error = "Failed to add building.";
            }
        }
    }
    
    // Add Spot
    elseif(isset($_POST['add_spot'])) {
        $building_id = intval($_POST['building_id']);
        $name = sanitizeInputs($_POST['spot_name']);
        
        // Ensure a valid building is provided
        if($building_id <= 0) {
            $error = "Please select a valid building.";
        } else {
            $stmt_check = $pdo->prepare("SELECT id FROM spot WHERE building_id = ? AND name = ?");
            $stmt_check->execute([$building_id, $name]);
            if($stmt_check->fetch()) {
                $error = "Spot already exists in this building.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO spot (building_id, name, status) VALUES (?, ?)");
                if($stmt->execute([$building_id, $name])) {
                    $success = "Spot added successfully.";
                } else {
                    $error = "Failed to add spot.";
                }
            }
        }
    }
}

$campuses = $pdo->query("SELECT * FROM campus ORDER BY id DESC")->fetchAll();
$buildings = $pdo->query("SELECT b.*, c.name as campus_name FROM building b JOIN campus c ON b.campus_id = c.id ORDER BY b.id DESC")->fetchAll();
$spots = $pdo->query("SELECT s.*, b.name as building_name, c.name as campus_name FROM spot s JOIN building b ON s.building_id = b.id JOIN campus c ON b.campus_id = c.id ORDER BY s.id DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Areas</h2>
</div>

<?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>

<div class="row">
    <!-- ADD CAMPUS -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary">Add Campus</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Campus Name</label>
                        <input type="text" name="campus_name" class="form-control" required>
                    </div>
                    <button type="submit" name="add_campus" class="btn btn-primary w-100">Add Campus</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- ADD BUILDING -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success">Add Building</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Select Campus</label>
                        <select name="campus_id" class="form-select" required>
                            <option value="">Select Campus...</option>
                            <?php foreach($campuses as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Building Name</label>
                        <input type="text" name="building_name" class="form-control" required>
                    </div>
                    <button type="submit" name="add_building" class="btn btn-success w-100">Add Building</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- ADD SPOT -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info">Add Spot</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Select Campus</label>
                        <select id="spot_campus_id" class="form-select" required>
                            <option value="">Select Campus...</option>
                            <?php foreach($campuses as $c): ?>
                                <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Building</label>
                        <select name="building_id" id="spot_building_id" class="form-select" required>
                            <option value="">Select Building First...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Spot Name</label>
                        <input type="text" name="spot_name" class="form-control" required>
                    </div>
                    <button type="submit" name="add_spot" class="btn btn-info text-white w-100">Add Spot</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- CAMPUSES LIST -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold">All Campuses</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($campuses)) echo '<li class="list-group-item text-muted">No campuses found.</li>'; ?>
                    <?php foreach($campuses as $c): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($c['name']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- BUILDINGS LIST -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold">All Buildings</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($buildings)) echo '<li class="list-group-item text-muted">No buildings found.</li>'; ?>
                    <?php foreach($buildings as $b): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong><?= htmlspecialchars($b['name']) ?></strong>
                            </div>
                            <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($b['campus_name']) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- SPOTS LIST -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light fw-bold">All Spots</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($spots)) echo '<li class="list-group-item text-muted">No spots found.</li>'; ?>
                    <?php foreach($spots as $s): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong><?= htmlspecialchars($s['name']) ?></strong>
                            </div>
                            <small class="text-muted"><i class="bi bi-building"></i> <?= htmlspecialchars($s['building_name']) ?> (<?= htmlspecialchars($s['campus_name']) ?>)</small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#spot_campus_id').change(function() {
        var campus_id = $(this).val();
        var buildingSelect = $('#spot_building_id');
        
        buildingSelect.html('<option value="">Loading...</option>');
        
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
                                options += '<option value="' + building.id + '">' + building.name + '</option>';
                            });
                        } else {
                            options = '<option value="">No buildings found</option>';
                        }
                        buildingSelect.html(options);
                    } else {
                        buildingSelect.html('<option value="">Error loading buildings</option>');
                    }
                },
                error: function() {
                    buildingSelect.html('<option value="">Error fetching data</option>');
                }
            });
        } else {
            buildingSelect.html('<option value="">Select Campus First...</option>');
        }
    });

    // Form client side validation
    $('form').submit(function(e) {
        var isValid = true;
        $(this).find('input[type="text"], select').each(function() {
            if ($.trim($(this).val()) == '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill out all required fields.');
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
