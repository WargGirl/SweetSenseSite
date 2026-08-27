function escapePreviewText(text) {
    return text.replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function textToPreviewHtml(text) {
    let html = escapePreviewText(text);
    html = html.replace(/\*\*(.*?)\*\*/gs, '<strong>$1</strong>');
    html = html.replace(/\*(.*?)\*/gs, '<em>$1</em>');
    return html.replace(/\r\n|\r|\n/g, '<br>');
}

function updateTextPreview(input) {
    const preview = document.getElementById(input.dataset.previewTarget);
    if (!preview) return;
    preview.innerHTML = textToPreviewHtml(input.value);
    preview.classList.toggle('is-empty', input.value.trim() === '');
}

function bindTextPreviews(root = document) {
    root.querySelectorAll('.preview-source').forEach(input => {
        if (input.dataset.previewBound === 'true') return;
        input.addEventListener('input', () => updateTextPreview(input));
        input.dataset.previewBound = 'true';
        updateTextPreview(input);
    });
}

function triggerError(element, message, scrollTarget = null) {
    element.setCustomValidity(message);
    const target = scrollTarget || element;
    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => {
        element.reportValidity();
    }, 250);
}

document.addEventListener('focusout', function(e) {
    if (e.target && e.target.classList.contains('row-input-search')) {
        const val = e.target.value.trim();
        if (val) {
            const exists = availableIngredients.some(item => item.toLowerCase() === val.toLowerCase());
            if (!exists) {
                e.target.setCustomValidity(MSG_ING_NOT_FOUND);
                e.target.reportValidity();
            } else {
                e.target.setCustomValidity('');
            }
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('recipe-main-form');
    bindTextPreviews(form);

    form.addEventListener('submit', function(e) {
        if (form.querySelector('input[name="action_delete"]') && form.submittedByDelete) {
            return;
        }

        form.querySelectorAll('input, select, textarea').forEach(el => el.setCustomValidity(''));

        const tUk = document.getElementById('title_uk');
        if (!tUk.value.trim()) {
            e.preventDefault();
            triggerError(tUk, MSG_REQUIRED);
            return;
        }

        const tEn = document.getElementById('title_en');
        if (!tEn.value.trim()) {
            e.preventDefault();
            triggerError(tEn, MSG_REQUIRED);
            return;
        }

        if (tUk.value.trim().toLowerCase() === tEn.value.trim().toLowerCase()) {
            e.preventDefault();
            triggerError(tEn, MSG_UNIQUE_TITLES);
            return;
        }

        const catSelect = document.getElementById('category_select');
        if (!catSelect.value) {
            e.preventDefault();
            triggerError(catSelect, MSG_REQUIRED);
            return;
        }

        const timeInput = document.getElementById('cooking_time');
        if (!timeInput.value || parseFloat(timeInput.value) <= 0) {
            e.preventDefault();
            triggerError(timeInput, MSG_REQUIRED);
            return;
        }

        const imgInput = document.getElementById('recipe_image_input');
        const imgLabel = document.getElementById('recipe_image_label');
        const hasExistingImg = form.dataset.hasImage === '1';

        if (!hasExistingImg && (!imgInput.files || imgInput.files.length === 0)) {
            e.preventDefault();
            imgLabel.classList.add('file-error');
            triggerError(imgInput, MSG_CHOOSE_PHOTO, imgLabel);
            return;
        } else {
            if (imgLabel) imgLabel.classList.remove('file-error');
        }

        const ingRows = form.querySelectorAll('.ingredient-row');
        for (let row of ingRows) {
            const nameInput = row.querySelector('.row-input-search');
            const amountInput = row.querySelector('.row-input-amount');
            const val = nameInput.value.trim();

            if (!val) {
                e.preventDefault();
                triggerError(nameInput, MSG_REQUIRED);
                return;
            }

            const exists = availableIngredients.some(item => item.toLowerCase() === val.toLowerCase());
            if (!exists) {
                e.preventDefault();
                triggerError(nameInput, MSG_ING_NOT_FOUND);
                return;
            }

            if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
                e.preventDefault();
                triggerError(amountInput, MSG_REQUIRED);
                return;
            }
        }

        const stepCards = form.querySelectorAll('.step-card');
        for (let step of stepCards) {
            const stepUk = step.querySelector('textarea[name*="[uk]"]');
            const stepEn = step.querySelector('textarea[name*="[en]"]');

            if (!stepUk.value.trim()) {
                e.preventDefault();
                triggerError(stepUk, MSG_REQUIRED);
                return;
            }

            if (!stepEn.value.trim()) {
                e.preventDefault();
                triggerError(stepEn, MSG_REQUIRED);
                return;
            }
        }
    });

    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input', () => el.setCustomValidity(''));
        el.addEventListener('change', () => el.setCustomValidity(''));
    });
});

function updateFileName(input) {
    const fileNameText = document.getElementById('file-chosen-text');
    const imgLabel = document.getElementById('recipe_image_label');
    if (input.files && input.files.length > 0) {
        fileNameText.textContent = input.files[0].name;
        input.setCustomValidity('');
        if (imgLabel) imgLabel.classList.remove('file-error');
    } else {
        fileNameText.textContent = MSG_FILE_NOT_CHOSEN;
    }
}

function updateStepFileName(input, index) {
    const textEl = document.getElementById('step-file-text-' + index);
    if (textEl) {
        textEl.textContent = (input.files && input.files.length > 0) 
            ? input.files[0].name 
            : MSG_FILE_NOT_CHOSEN;
    }
}

function showDropdown(input) {
    input.setCustomValidity('');
    const list = input.parentElement.querySelector('.autocomplete-list');
    renderOptions(list, input.value.trim(), input);
    list.classList.add('show');
}

function filterDropdown(input) {
    input.setCustomValidity('');
    const list = input.parentElement.querySelector('.autocomplete-list');
    renderOptions(list, input.value.trim(), input);
    list.classList.add('show');
}

function renderOptions(listElement, query, targetInput) {
    listElement.innerHTML = '';
    const filtered = availableIngredients.filter(item => 
        item.toLowerCase().includes(query.toLowerCase())
    );

    if (filtered.length === 0) {
        listElement.classList.remove('show');
        return;
    }

    filtered.forEach(item => {
        const itemDiv = document.createElement('div');
        itemDiv.className = 'autocomplete-item';
        itemDiv.textContent = item;
        itemDiv.onmousedown = function(e) {
            e.preventDefault();
            targetInput.value = item;
            targetInput.setCustomValidity('');
            listElement.classList.remove('show');
        };
        listElement.appendChild(itemDiv);
    });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.autocomplete-wrapper')) {
        document.querySelectorAll('.autocomplete-list').forEach(el => el.classList.remove('show'));
    }
});

function addIngredientRow() {
    const container = document.getElementById('ingredients-container');
    const row = document.createElement('div');
    row.className = 'dynamic-row ingredient-row';

    let unitOptions = '';
    availableUnits.forEach(u => {
        const label = isUkrainian ? u.short_uk : u.short_en;
        unitOptions += `<option value="${u.id}">${label}</option>`;
    });

    row.innerHTML = `
        <div class="autocomplete-wrapper">
            <input type="text" name="ingredients[${ingredientIndex}][name]" class="form-control row-input-search" placeholder="${MSG_CHOOSE_ING}" autocomplete="off" onfocus="showDropdown(this)" oninput="filterDropdown(this)">
            <div class="autocomplete-list"></div>
        </div>
        <input type="number" step="any" min="0.01" name="ingredients[${ingredientIndex}][amount]" class="form-control row-input-amount" placeholder="${MSG_AMOUNT_PH}" autocomplete="off">
        <select name="ingredients[${ingredientIndex}][unit_id]" class="form-control row-select-unit">
            ${unitOptions}
        </select>
        <button type="button" class="btn-icon btn-danger" onclick="removeDynamicRow(this)" title="Delete">🗑️</button>
    `;
    container.appendChild(row);
    ingredientIndex++;
}

function removeDynamicRow(btn) {
    const rows = document.querySelectorAll('.ingredient-row');
    if (rows.length > 1) {
        btn.closest('.dynamic-row').remove();
    }
}

function addStepRow() {
    const container = document.getElementById('steps-container');
    const totalSteps = container.querySelectorAll('.step-card').length + 1;
    const card = document.createElement('div');
    card.className = 'step-card';
    card.innerHTML = `
        <div class="step-card-header">
            <div class="step-header-left">
                <span class="step-badge">${MSG_STEP_TITLE} ${totalSteps}</span>
                <div class="custom-file-wrapper step-file-wrapper">
                    <label class="custom-file-btn btn-sm" for="step_image_${stepIndex}">${MSG_STEP_PHOTO}</label>
                    <span id="step-file-text-${stepIndex}" class="file-name-text">${MSG_FILE_NOT_CHOSEN}</span>
                    <input type="file" id="step_image_${stepIndex}" name="step_images[${stepIndex}]" class="hidden-file-input" accept=".jpg,.jpeg,.png,.webp" onchange="updateStepFileName(this, ${stepIndex})">
                </div>
            </div>
            <button type="button" class="btn-icon btn-danger" onclick="removeStepRow(this)">🗑️</button>
        </div>
        <div class="form-row-two">
            <div class="preview-field">
                <textarea name="steps[${stepIndex}][uk]" class="form-control textarea-step preview-source" data-preview-target="preview_step_${stepIndex}_uk" placeholder="${MSG_STEP_UK}" autocomplete="off"></textarea>
                <div id="preview_step_${stepIndex}_uk" class="text-preview text-preview-step" data-label="${MSG_PREVIEW_LABEL}" aria-live="polite"></div>
            </div>
            <div class="preview-field">
                <textarea name="steps[${stepIndex}][en]" class="form-control textarea-step preview-source" data-preview-target="preview_step_${stepIndex}_en" placeholder="${MSG_STEP_EN}" autocomplete="off"></textarea>
                <div id="preview_step_${stepIndex}_en" class="text-preview text-preview-step" data-label="${MSG_PREVIEW_LABEL}" aria-live="polite"></div>
            </div>
        </div>
        <div class="form-row-two mt-10">
            <div class="preview-field">
                <textarea name="steps[${stepIndex}][tip_uk]" class="form-control textarea-tip preview-source" data-preview-target="preview_tip_${stepIndex}_uk" placeholder="${MSG_STEP_TIP_UK}" autocomplete="off"></textarea>
                <div id="preview_tip_${stepIndex}_uk" class="text-preview text-preview-step" data-label="${MSG_PREVIEW_LABEL}" aria-live="polite"></div>
            </div>
            <div class="preview-field">
                <textarea name="steps[${stepIndex}][tip_en]" class="form-control textarea-tip preview-source" data-preview-target="preview_tip_${stepIndex}_en" placeholder="${MSG_STEP_TIP_EN}" autocomplete="off"></textarea>
                <div id="preview_tip_${stepIndex}_en" class="text-preview text-preview-step" data-label="${MSG_PREVIEW_LABEL}" aria-live="polite"></div>
            </div>
        </div>
    `;
    container.appendChild(card);
    bindTextPreviews(card);
    stepIndex++;
}

function removeStepRow(btn) {
    const steps = document.querySelectorAll('.step-card');
    if (steps.length > 1) {
        btn.closest('.step-card').remove();
        renumberSteps();
    }
}

function renumberSteps() {
    const badges = document.querySelectorAll('#steps-container .step-badge');
    badges.forEach((badge, i) => {
        badge.innerText = `${MSG_STEP_TITLE} ${i + 1}`;
    });
}