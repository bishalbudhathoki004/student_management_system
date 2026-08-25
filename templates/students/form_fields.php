<?php
$isEdit = $isEdit ?? false;
$studentData = $studentData ?? [];
$submitLabel = $submitLabel ?? 'Save';
$cancelUrl = $cancelUrl ?? 'manage_students.php';
$showPhoto = $showPhoto ?? false;
?>
<?php if ($showPhoto): ?>
    <div class="form-group">
        <label for="photo">Photo (JPG/PNG/WEBP)</label>
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
    </div>
<?php endif; ?>

<?php if (!$isEdit): ?>
    <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" value="<?php echo e($studentData['name'] ?? ''); ?>" required>
    </div>
<?php else: ?>
    <h2 class="student-name"><?php echo e($studentData['name'] ?? '-'); ?></h2>
<?php endif; ?>

<div class="form-grid">
    <div class="form-group">
        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
            <option value="">Select Gender</option>
            <?php foreach (student_genders() as $gender): ?>
                <option value="<?php echo e($gender); ?>" <?php echo (($studentData['gender'] ?? '') === $gender) ? 'selected' : ''; ?>>
                    <?php echo e($gender); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="faculty">Faculty</label>
        <select id="faculty" name="faculty" required>
            <option value="">Select Faculty</option>
            <?php foreach (student_faculties() as $faculty): ?>
                <option value="<?php echo e($faculty); ?>" <?php echo (($studentData['faculty'] ?? '') === $faculty) ? 'selected' : ''; ?>>
                    <?php echo e($faculty); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" value="<?php echo e($studentData['dob'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
        <label for="address">Address</label>
        <input type="text" id="address" name="address" value="<?php echo e($studentData['address'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" maxlength="10" value="<?php echo e($studentData['phone'] ?? ''); ?>" required>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo e($studentData['email'] ?? ''); ?>" required>
    </div>
</div>

<div class="actions">
    <button type="submit" class="btn btn-primary"><?php echo e($submitLabel); ?></button>
    <a href="<?php echo e($cancelUrl); ?>" class="btn btn-secondary">Cancel</a>
</div>
