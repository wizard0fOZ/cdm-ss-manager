<?php
  $csrf = $_SESSION['_csrf'] ?? '';

  $firstName = $student['first_name'] ?? '';
  $lastName = $student['last_name'] ?? '';
  if (($firstName === '' || $lastName === '') && !empty($student['full_name'] ?? '')) {
    $parts = preg_split('/\s+/', trim($student['full_name']));
    $firstName = $firstName ?: ($parts[0] ?? '');
    $lastName = $lastName ?: (count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '');
  }

  $dob = $student['dob'] ?? '';
  $identityNumber = $student['identity_number'] ?? '';
  $status = $student['status'] ?? 'ACTIVE';
  $address = $student['address'] ?? '';
  $notes = $student['notes'] ?? '';
  $selectedClassId = $student['class_id'] ?? '';

  $sacrament = $sacrament ?? [];

  $guardians = $guardians ?? [];
  if (!$guardians) {
    $guardians = [
      ['full_name' => '', 'relationship_label' => 'Father', 'phone' => '', 'email' => '', 'is_primary' => 1],
      ['full_name' => '', 'relationship_label' => 'Mother', 'phone' => '', 'email' => '', 'is_primary' => 0],
    ];
  }

  while (count($guardians) < 2) {
    $guardians[] = ['full_name' => '', 'relationship_label' => '', 'phone' => '', 'email' => '', 'is_primary' => 0];
  }
?>

<?php if (!empty($errors)): ?>
  <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
    <ul class="list-disc pl-5">
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="<?= htmlspecialchars($action) ?>" class="space-y-8">
  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

  <section class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">First Name</label>
      <input name="first_name" value="<?= htmlspecialchars($firstName) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
    <div>
      <label class="text-sm text-slate-600">Last Name</label>
      <input name="last_name" value="<?= htmlspecialchars($lastName) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" required>
    </div>
    <div>
      <label class="text-sm text-slate-600">Date of Birth</label>
      <input type="date" name="dob" value="<?= htmlspecialchars($dob) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      <p class="mt-1 text-xs text-slate-400">Optional, but needed for Age + FHC guidance.</p>
    </div>
    <div>
      <div class="flex items-center justify-between">
        <label class="text-sm text-slate-600">Class</label>
        <a href="/classes" class="text-xs text-slate-500 hover:text-slate-700">Create class</a>
      </div>
      <select name="class_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
        <option value="">Select a class</option>
        <?php foreach ($classes as $class): ?>
          <option value="<?= (int)$class['id'] ?>" <?= (string)$selectedClassId === (string)$class['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($class['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-sm text-slate-600">Identity Number</label>
      <input name="identity_number" value="<?= htmlspecialchars($identityNumber) ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
    </div>
    <div>
      <label class="text-sm text-slate-600">Status</label>
      <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2" data-enhance="search">
        <?php foreach (['ACTIVE','INACTIVE','GRADUATED','TRANSFERRED'] as $opt): ?>
          <option value="<?= $opt ?>" <?= $status === $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </section>

  <section>
    <div class="text-sm font-semibold text-slate-900">Father's Information</div>
    <div class="mt-3 grid gap-4 md:grid-cols-3" data-guardian-row>
      <?php
        $father = $guardians[0] ?? ['full_name' => '', 'relationship_label' => 'Father', 'phone' => '', 'email' => '', 'is_primary' => 1];
      ?>
      <input type="hidden" name="guardians[0][relationship_label]" value="Father">
      <div>
        <label class="text-sm text-slate-600">Father's Name</label>
        <input name="guardians[0][full_name]" value="<?= htmlspecialchars($father['full_name'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <div>
        <label class="text-sm text-slate-600">Father's Email</label>
        <input name="guardians[0][email]" value="<?= htmlspecialchars($father['email'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <div>
        <label class="text-sm text-slate-600">Father's Phone</label>
        <input name="guardians[0][phone]" value="<?= htmlspecialchars($father['phone'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <input type="hidden" name="guardians[0][is_primary]" value="1">
    </div>
  </section>

  <section>
    <div class="text-sm font-semibold text-slate-900">Mother's Information</div>
    <div class="mt-3 grid gap-4 md:grid-cols-3" data-guardian-row>
      <?php
        $mother = $guardians[1] ?? ['full_name' => '', 'relationship_label' => 'Mother', 'phone' => '', 'email' => '', 'is_primary' => 0];
      ?>
      <input type="hidden" name="guardians[1][relationship_label]" value="Mother">
      <div>
        <label class="text-sm text-slate-600">Mother's Name</label>
        <input name="guardians[1][full_name]" value="<?= htmlspecialchars($mother['full_name'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <div>
        <label class="text-sm text-slate-600">Mother's Email</label>
        <input name="guardians[1][email]" value="<?= htmlspecialchars($mother['email'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <div>
        <label class="text-sm text-slate-600">Mother's Phone</label>
        <input name="guardians[1][phone]" value="<?= htmlspecialchars($mother['phone'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
    </div>
  </section>

  <section>
    <div class="flex items-center justify-between">
      <div class="text-sm font-semibold text-slate-900">Additional Guardians</div>
      <button type="button" class="rounded-lg border border-slate-200 px-3 py-1 text-xs" data-add-guardian>Add guardian</button>
    </div>

    <div class="mt-3 space-y-3" id="guardian-list">
      <?php foreach (array_slice($guardians, 2) as $i => $guardian):
        $index = $i + 2;
      ?>
        <div class="grid gap-3 md:grid-cols-5 border border-slate-200 rounded-xl p-3">
          <div>
            <label class="text-xs text-slate-500">Name</label>
            <input name="guardians[<?= $index ?>][full_name]" value="<?= htmlspecialchars($guardian['full_name'] ?? '') ?>" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
          </div>
          <div>
            <label class="text-xs text-slate-500">Relationship</label>
            <input name="guardians[<?= $index ?>][relationship_label]" value="<?= htmlspecialchars($guardian['relationship_label'] ?? '') ?>" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
          </div>
          <div>
            <label class="text-xs text-slate-500">Email</label>
            <input name="guardians[<?= $index ?>][email]" value="<?= htmlspecialchars($guardian['email'] ?? '') ?>" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
          </div>
          <div>
            <label class="text-xs text-slate-500">Phone</label>
            <input name="guardians[<?= $index ?>][phone]" value="<?= htmlspecialchars($guardian['phone'] ?? '') ?>" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
          </div>
          <div class="flex items-end justify-end">
            <button type="button" class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50" data-remove-guardian aria-label="Remove guardian">
              <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 6h18"/>
                <path d="M8 6V4h8v2"/>
                <path d="M8 10v8"/>
                <path d="M12 10v8"/>
                <path d="M16 10v8"/>
              </svg>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="space-y-4">
    <div>
      <div class="text-sm font-semibold text-slate-900">Church & Sacrament Information</div>
      <p class="text-xs text-slate-500 mt-1">Leave blank if not applicable (e.g., RCIC or younger students).</p>
    </div>

    <div>
      <?php $isRcic = !empty($student['is_rcic'] ?? ($_POST['is_rcic'] ?? null)); ?>
      <label class="inline-flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="is_rcic" value="1" <?= $isRcic ? 'checked' : '' ?> data-rcic-toggle>
        RCIC (student is in RCIC / converting, no sacraments yet)
      </label>
    </div>

    <div class="grid gap-4 md:grid-cols-2" data-sacrament-field>
      <div class="md:col-span-2 text-xs uppercase tracking-wide text-slate-500">Baptism</div>
      <div>
        <label class="text-sm text-slate-600">Church of Baptism</label>
        <input name="church_of_baptism" value="<?= htmlspecialchars($sacrament['church_of_baptism'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <div>
        <label class="text-sm text-slate-600">Place of Baptism</label>
        <input name="place_of_baptism" value="<?= htmlspecialchars($sacrament['place_of_baptism'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <div>
      <label class="text-sm text-slate-600">Date of Baptism</label>
      <input type="date" name="date_of_baptism" value="<?= htmlspecialchars($sacrament['date_of_baptism'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <div>
        <label class="text-sm text-slate-600">Godfather</label>
        <input name="godfather" value="<?= htmlspecialchars($sacrament['godfather'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <div>
        <label class="text-sm text-slate-600">Godmother</label>
        <input name="godmother" value="<?= htmlspecialchars($sacrament['godmother'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2" data-sacrament-field>
      <div class="md:col-span-2 text-xs uppercase tracking-wide text-slate-500">First Holy Communion (FHC)</div>
    <div>
      <label class="text-sm text-slate-600">Date of First Holy Communion</label>
      <input type="date" name="date_of_fhc" value="<?= htmlspecialchars($sacrament['date_of_first_holy_communion'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      <p class="mt-1 text-xs text-slate-400">Usually around age 12–13. Leave blank if not yet.</p>
    </div>
      <div>
        <label class="text-sm text-slate-600">Place of FHC</label>
        <input name="place_of_fhc" value="<?= htmlspecialchars($sacrament['place_of_first_holy_communion'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2" data-sacrament-field>
      <div class="md:col-span-2 text-xs uppercase tracking-wide text-slate-500">Confirmation</div>
      <div>
      <label class="text-sm text-slate-600">Date of Confirmation</label>
      <input type="date" name="date_of_confirmation" value="<?= htmlspecialchars($sacrament['date_of_confirmation'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
      <div>
        <label class="text-sm text-slate-600">Place of Confirmation</label>
        <input name="place_of_confirmation" value="<?= htmlspecialchars($sacrament['place_of_confirmation'] ?? '') ?>" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2">
      </div>
    </div>
  </section>

  <section class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="text-sm text-slate-600">Remarks</label>
      <textarea name="notes" rows="3" placeholder="Any special remarks..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2"><?= htmlspecialchars($notes) ?></textarea>
    </div>
    <div>
      <label class="text-sm text-slate-600">Address / Notes</label>
      <textarea name="address" rows="3" placeholder="Address or any additional notes..." class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2"><?= htmlspecialchars($address) ?></textarea>
    </div>
  </section>

  <div class="flex items-center gap-3">
    <button class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="submit">
      <?= htmlspecialchars($submitLabel) ?>
    </button>
    <a href="/students" class="text-sm text-slate-600">Cancel</a>
  </div>
</form>

<template id="guardian-template">
  <div class="grid gap-3 md:grid-cols-5 border border-slate-200 rounded-xl p-3">
    <div>
      <label class="text-xs text-slate-500">Name</label>
      <input data-name="full_name" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
    </div>
    <div>
      <label class="text-xs text-slate-500">Relationship</label>
      <input data-name="relationship_label" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
    </div>
    <div>
      <label class="text-xs text-slate-500">Email</label>
      <input data-name="email" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
    </div>
    <div>
      <label class="text-xs text-slate-500">Phone</label>
      <input data-name="phone" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2">
    </div>
    <div class="flex items-end justify-end">
      <button type="button" class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50" data-remove-guardian aria-label="Remove guardian">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 6h18"/>
          <path d="M8 6V4h8v2"/>
          <path d="M8 10v8"/>
          <path d="M12 10v8"/>
          <path d="M16 10v8"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script>
  (function () {
    const addBtn = document.querySelector('[data-add-guardian]');
    const list = document.getElementById('guardian-list');
    const template = document.getElementById('guardian-template');
    const rcicToggle = document.querySelector('[data-rcic-toggle]');
    const sacramentFields = document.querySelectorAll('[data-sacrament-field]');

    if (!addBtn || !list || !template) return;

    let index = <?= (int)count($guardians) ?>;

    addBtn.addEventListener('click', function () {
      const node = template.content.cloneNode(true);
      node.querySelectorAll('input[data-name]').forEach((input) => {
        const key = input.getAttribute('data-name');
        input.name = `guardians[${index}][${key}]`;
      });
      const removeBtn = node.querySelector('[data-remove-guardian]');
      if (removeBtn) {
        removeBtn.addEventListener('click', function (event) {
          const wrapper = event.target.closest('.grid');
          if (wrapper) wrapper.remove();
        });
      }
      list.appendChild(node);
      index += 1;
    });

    function setSacramentVisibility(isRcic) {
      sacramentFields.forEach((field) => {
        field.style.display = isRcic ? 'none' : 'block';
      });
    }

    if (rcicToggle) {
      setSacramentVisibility(rcicToggle.checked);
      rcicToggle.addEventListener('change', () => setSacramentVisibility(rcicToggle.checked));
    }

    document.querySelectorAll('[data-remove-guardian]').forEach((btn) => {
      btn.addEventListener('click', (event) => {
        const wrapper = event.target.closest('.grid');
        if (wrapper) wrapper.remove();
      });
    });
  })();
</script>
