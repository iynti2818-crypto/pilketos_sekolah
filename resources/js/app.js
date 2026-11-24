import './bootstrap';

document.querySelectorAll('[data-candidate-photo]').forEach((image) => {
    const fallback = image.nextElementSibling;

    if (fallback) {
        image.addEventListener('error', () => {
            image.hidden = true;
            fallback.hidden = false;
        });
    }
});

const voteConfirmModal = document.querySelector('[data-vote-confirm]');

if (voteConfirmModal) {
    const cancelButton = voteConfirmModal.querySelector('[data-vote-cancel]');
    const confirmButton = voteConfirmModal.querySelector('[data-vote-submit]');
    const candidateLabel = voteConfirmModal.querySelector('[data-vote-candidate]');
    let pendingForm = null;
    let triggerButton = null;

    const closeVoteConfirm = () => {
        voteConfirmModal.hidden = true;
        pendingForm = null;

        if (triggerButton?.isConnected) {
            triggerButton.focus();
        }

        triggerButton = null;
    };

    document.querySelectorAll('[data-vote-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            pendingForm = form;
            triggerButton = event.submitter || form.querySelector('button[type="submit"]');
            candidateLabel.textContent = `Pasangan calon nomor urut ${triggerButton.dataset.candidateNumber}: ${triggerButton.dataset.candidateName}`;
            voteConfirmModal.hidden = false;
            cancelButton.focus();
        });
    });

    cancelButton.addEventListener('click', closeVoteConfirm);
    confirmButton.addEventListener('click', () => {
        if (!pendingForm) {
            closeVoteConfirm();
            return;
        }

        const form = pendingForm;
        pendingForm = null;
        form.dataset.confirmed = 'true';
        closeVoteConfirm();
        form.requestSubmit();
    });

    voteConfirmModal.addEventListener('click', (event) => {
        if (event.target === voteConfirmModal) {
            closeVoteConfirm();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!voteConfirmModal.hidden && event.key === 'Escape') {
            closeVoteConfirm();
        }
    });
}

document.querySelectorAll('[data-photo-paste]').forEach((area) => {
    const input = area.closest('form')?.querySelector('[data-photo-input]');
    const preview = area.querySelector('[data-photo-preview]');
    const label = area.querySelector('[data-photo-label]');
    const error = area.querySelector('[data-photo-error]');
    const urlInput = document.querySelector('[data-photo-url]');

    if (!input || !preview || !label || !error || !urlInput) {
        return;
    }

    const showFile = (file) => {
        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            error.textContent = 'Berkas tempelan bukan gambar. Pilih atau tempel file gambar.';
            error.hidden = false;
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            error.textContent = 'Ukuran gambar melebihi batas 5 MB.';
            error.hidden = false;
            return;
        }

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        urlInput.value = '';
        error.hidden = true;
        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
        label.textContent = file.name;
    };

    input.addEventListener('change', () => showFile(input.files[0]));

    preview.addEventListener('error', () => {
        error.textContent = 'Tautan ini tidak menampilkan file gambar. Gunakan tautan langsung ke gambar atau unggah file.';
        error.hidden = false;
    });

    preview.addEventListener('load', () => {
        error.hidden = true;
    });

    const showUrl = (value) => {
        const url = value.trim();

        if (!url) {
            return false;
        }

        try {
            const parsedUrl = new URL(url, window.location.href);
            if (!['http:', 'https:'].includes(parsedUrl.protocol)) {
                return false;
            }

            urlInput.value = parsedUrl.href;
            urlInput.dispatchEvent(new Event('input', { bubbles: true }));
            label.textContent = 'Link foto ditempel';
            return true;
        } catch {
            return false;
        }
    };

    urlInput.addEventListener('input', () => {
        const url = urlInput.value.trim();
        error.hidden = true;
        if (url) {
            preview.src = url;
            preview.hidden = false;
        } else if (!input.files.length) {
            preview.hidden = true;
        }
    });

    area.addEventListener('paste', (event) => {
        const image = Array.from(event.clipboardData.items)
            .find((item) => item.type.startsWith('image/'));

        if (image) {
            event.preventDefault();
            showFile(image.getAsFile());
            return;
        }

        const html = event.clipboardData.getData('text/html');
        const pastedImageUrl = html
            ? new DOMParser().parseFromString(html, 'text/html').querySelector('img')?.src
            : null;
        const text = event.clipboardData.getData('text/plain').trim();

        event.preventDefault();
        if (pastedImageUrl && showUrl(pastedImageUrl)) {
            return;
        }

        if (showUrl(text)) {
            return;
        }

        error.textContent = 'Tempel gambar atau tautan langsung ke file gambar (.jpg, .png, atau .webp).';
        error.hidden = false;
    });

    area.addEventListener('dragover', (event) => event.preventDefault());
    area.addEventListener('drop', (event) => {
        event.preventDefault();
        showFile(event.dataTransfer.files[0]);
    });
});
