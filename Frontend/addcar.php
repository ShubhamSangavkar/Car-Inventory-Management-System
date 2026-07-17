<?php
    include "home.php";

    $success = "";
    $errors  = [];

    if (isset($_POST['add_car'])) {
        $name         = trim($_POST['name']);
        $color        = trim($_POST['color']);
        $number_plate = strtoupper(trim($_POST['number_plate']));
        $description  = trim($_POST['description']);
        $capacity     = intval($_POST['capacity']);
        $model        = trim($_POST['model']);

        // Validation
        if (empty($name))         $errors[] = "Car name is required.";
        if (empty($color))        $errors[] = "Color is required.";
        if (empty($number_plate)) $errors[] = "Number plate is required.";
        if (empty($model))        $errors[] = "Model is required.";
        if ($capacity < 1 || $capacity > 20) $errors[] = "Capacity must be between 1 and 20.";

        // Check duplicate number plate
        if (empty($errors)) {
            $plate_check = mysqli_query($conn, "SELECT car_id FROM cars WHERE number_plate = '$number_plate'");
            if (mysqli_num_rows($plate_check) > 0) {
                $errors[] = "A car with this number plate already exists.";
            }
        }

        if (empty($errors)) {
            $name         = mysqli_real_escape_string($conn, $name);
            $color        = mysqli_real_escape_string($conn, $color);
            $number_plate = mysqli_real_escape_string($conn, $number_plate);
            $description  = mysqli_real_escape_string($conn, $description);
            $model        = mysqli_real_escape_string($conn, $model);

            $insert = "INSERT INTO cars (owner_id, name, color, number_plate, description, capacity, model)
                       VALUES ($user_id, '$name', '$color', '$number_plate', '$description', $capacity, '$model')";

            if (mysqli_query($conn, $insert)) {
                $success = "Your car <strong>$name</strong> has been added successfully!";
            } else {
                $errors[] = "Database error. Please try again.";
            }
        }
    }
?>

<section class="mainpart">
<div class="addcar-wrapper">

    <div class="form-hero">
        <div class="hero-icon">🚗</div>
        <h2>Add Your Car</h2>
        <p>List your vehicle on CarVista and start selling or renting</p>
    </div>

    <?php if ($success != ""): ?>
        <div class="flash flash-success">
            <span class="flash-icon">✅</span>
            <div>
                <?= $success ?>
                <br><a href="mycar.php">View My Cars →</a>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="flash flash-error">
            <span class="flash-icon">⚠️</span>
            <div>
                <?php foreach ($errors as $e): ?>
                    <div><?= $e ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" class="car-form" id="carForm">

        <div class="form-grid">

            <!-- Car Name -->
            <div class="field">
                <label for="name">
                    <span class="field-icon">🏷️</span> Car Name
                </label>
                <input type="text" id="name" name="name"
                       placeholder="e.g. Toyota Fortuner"
                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                       required>
                <span class="field-hint">Brand + common name</span>
            </div>

            <!-- Model -->
            <div class="field">
                <label for="model">
                    <span class="field-icon">📐</span> Model / Year
                </label>
                <input type="text" id="model" name="model"
                       placeholder="e.g. Fortuner 4x4 2022"
                       value="<?= isset($_POST['model']) ? htmlspecialchars($_POST['model']) : '' ?>"
                       required>
                <span class="field-hint">Include year if possible</span>
            </div>

            <!-- Color -->
            <div class="field">
                <label for="color">
                    <span class="field-icon">🎨</span> Color
                </label>
                <div class="color-input-wrap">
                    <input type="text" id="color" name="color"
                           placeholder="e.g. Pearl White"
                           value="<?= isset($_POST['color']) ? htmlspecialchars($_POST['color']) : '' ?>"
                           required>
                    <div class="color-swatches">
                        <?php
                        $swatches = ['White','Black','Silver','Red','Blue','Grey','Green','Brown','Orange','Yellow'];
                        foreach ($swatches as $sw):
                        ?>
                        <button type="button" class="swatch"
                                style="background:<?= strtolower($sw) ?>;"
                                title="<?= $sw ?>"
                                onclick="document.getElementById('color').value='<?= $sw ?>'">
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Number Plate -->
            <div class="field">
                <label for="number_plate">
                    <span class="field-icon">🔢</span> Number Plate
                </label>
                <input type="text" id="number_plate" name="number_plate"
                       placeholder="e.g. MH12AB1234"
                       value="<?= isset($_POST['number_plate']) ? htmlspecialchars($_POST['number_plate']) : '' ?>"
                       required
                       oninput="this.value=this.value.toUpperCase()"
                       style="font-family: 'Courier New', monospace; letter-spacing: 3px; font-weight: 700;">
                <span class="field-hint">Auto-converts to uppercase</span>
            </div>

            <!-- Capacity -->
            <div class="field">
                <label for="capacity">
                    <span class="field-icon">👥</span> Seating Capacity
                </label>
                <div class="capacity-wrap">
                    <button type="button" class="cap-btn" onclick="changeCapacity(-1)">−</button>
                    <input type="number" id="capacity" name="capacity"
                           value="<?= isset($_POST['capacity']) ? intval($_POST['capacity']) : 5 ?>"
                           min="1" max="20" required readonly>
                    <button type="button" class="cap-btn" onclick="changeCapacity(1)">+</button>
                </div>
                <span class="field-hint">Number of passengers including driver</span>
            </div>

            <!-- Description (full width) -->
            <div class="field field-full">
                <label for="description">
                    <span class="field-icon">📝</span> Description
                    <span class="optional-tag">Optional</span>
                </label>
                <textarea id="description" name="description"
                          placeholder="Describe your car — condition, features, fuel type, kilometers driven, etc."
                          rows="4"
                          maxlength="500"
                          oninput="updateCount(this)"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                <div class="char-counter">
                    <span id="charCount">0</span>/500 characters
                </div>
            </div>

        </div>

       

        <button type="submit" name="add_car" class="btn-submit" id="submitBtn">
            <span class="btn-text">Add Car to CarVista</span>
            <span class="btn-arrow">→</span>
        </button>

    </form>

</div>
</section>

<script>
// Live preview
const fields = {
    name:         { input: 'name',         preview: 'prevName',  default: 'Car Name' },
    model:        { input: 'model',        preview: 'prevModel', default: 'Model'    },
    color:        { input: 'color',        preview: 'prevColor', default: 'Color'    },
    number_plate: { input: 'number_plate', preview: 'prevPlate', default: 'PLATE'    },
    capacity:     { input: 'capacity',     preview: 'prevCap',   default: '5'        },
};

Object.values(fields).forEach(f => {
    const el = document.getElementById(f.input);
    if (el) el.addEventListener('input', updatePreview);
});

function updatePreview() {
    Object.values(fields).forEach(f => {
        const val = document.getElementById(f.input)?.value.trim();
        document.getElementById(f.preview).textContent = val || f.default;
    });
}

// Capacity stepper
function changeCapacity(delta) {
    const input = document.getElementById('capacity');
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(20, val));
    input.value = val;
    document.getElementById('prevCap').textContent = val;
}

// Char counter
function updateCount(el) {
    document.getElementById('charCount').textContent = el.value.length;
}

// Init preview on load
updatePreview();
updateCount(document.getElementById('description'));

// Submit animation
document.getElementById('carForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<span class="btn-text">Adding...</span><span class="btn-arrow">⏳</span>';
    btn.disabled = true;
});
</script>

<style>
/* ── Layout ───────────────────────────────────────────── */
.addcar-wrapper {
    max-width: 720px;
}

/* ── Hero ─────────────────────────────────────────────── */
.form-hero {
    margin-bottom: 28px;
}
.hero-icon {
    font-size: 2.5rem;
    margin-bottom: 8px;
    display: block;
}
.form-hero h2 {
    font-size: 1.9rem;
    color: #993333;
    font-weight: 800;
    margin-bottom: 6px;
}
.form-hero p {
    color: #777;
    font-size: 0.95rem;
    text-transform: none;
}

/* ── Flash Messages ───────────────────────────────────── */
.flash {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-size: 0.93rem;
    font-weight: 600;
    text-transform: none;
}
.flash-icon { font-size: 1.3rem; flex-shrink: 0; }
.flash-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.flash-success a { color: #155724; font-weight: 700; }
.flash-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

/* ── Form Card ────────────────────────────────────────── */
.car-form {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 6px 30px rgba(0,0,0,0.09);
    border: 1px solid #eee;
}

/* ── Grid ─────────────────────────────────────────────── */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px 24px;
    margin-bottom: 22px;
}
.field-full { grid-column: 1 / -1; }

/* ── Field ────────────────────────────────────────────── */
.field { display: flex; flex-direction: column; gap: 6px; }

.field label {
    font-size: 0.88rem;
    font-weight: 700;
    color: #333;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: none;
}
.field-icon { font-size: 1rem; }
.optional-tag {
    margin-left: auto;
    background: #f0f0f0;
    color: #999;
    font-size: 0.72rem;
    padding: 2px 8px;
    border-radius: 10px;
    font-weight: 600;
}
.field-hint {
    font-size: 0.75rem;
    color: #aaa;
    text-transform: none;
}

.field input,
.field textarea {
    padding: 12px 15px;
    border: 1.5px solid #e0e0e0;
    border-radius: 10px;
    font-size: 0.93rem;
    background: #fafafa;
    color: #333;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    font-family: 'Poppins', sans-serif;
    resize: vertical;
}
.field input:focus,
.field textarea:focus {
    border-color: #993333;
    background: white;
    outline: none;
    box-shadow: 0 0 0 3px rgba(153,51,51,0.1);
}

/* ── Color Swatches ───────────────────────────────────── */
.color-input-wrap { display: flex; flex-direction: column; gap: 8px; }
.color-swatches {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.swatch {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.15s, border-color 0.15s;
    padding: 0;
}
.swatch:hover {
    transform: scale(1.3);
    border-color: #993333;
}

/* ── Capacity Stepper ─────────────────────────────────── */
.capacity-wrap {
    display: flex;
    align-items: center;
    gap: 0;
}
.cap-btn {
    width: 40px; height: 44px;
    background: #f5f5f5;
    border: 1.5px solid #e0e0e0;
    font-size: 1.3rem;
    font-weight: 700;
    cursor: pointer;
    color: #993333;
    transition: background 0.15s;
    line-height: 1;
}
.cap-btn:first-child { border-radius: 10px 0 0 10px; border-right: none; }
.cap-btn:last-child  { border-radius: 0 10px 10px 0; border-left: none; }
.cap-btn:hover { background: #ffe5e5; }
.capacity-wrap input {
    width: 60px;
    text-align: center;
    border-radius: 0 !important;
    border-left: none;
    border-right: none;
    font-weight: 700;
    font-size: 1rem;
}

/* ── Char Counter ─────────────────────────────────────── */
.char-counter {
    font-size: 0.75rem;
    color: #bbb;
    text-align: right;
    text-transform: none;
}

/* ── Preview Strip ────────────────────────────────────── */
.preview-strip {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 12px;
    padding: 14px 20px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.preview-label {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #666;
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.5);
    padding: 3px 10px;
    border-radius: 6px;
    white-space: nowrap;
    font-weight: 600;
}
.preview-content {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 0.88rem;
    color: rgba(255,255,255,0.85);
    text-transform: none;
}
.preview-name { color: white; font-weight: 700; font-size: 1rem; }
.preview-sep  { color: rgba(255,255,255,0.3); }

/* ── Submit Button ────────────────────────────────────── */
.btn-submit {
    width: 100%;
    padding: 16px 24px;
    background: linear-gradient(135deg, #993333 0%, #cc4444 50%, #993333 100%);
    background-size: 200% 200%;
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    transition: transform 0.2s, box-shadow 0.2s;
    font-family: 'Poppins', sans-serif;
}
.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(153,51,51,0.45);
}
.btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-arrow { font-size: 1.2rem; transition: transform 0.2s; }
.btn-submit:hover .btn-arrow { transform: translateX(4px); }

/* ── Responsive ───────────────────────────────────────── */
@media (max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
    .car-form  { padding: 22px 18px; }
}
</style>