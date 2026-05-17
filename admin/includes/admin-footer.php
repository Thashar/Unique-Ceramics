  </div><!-- /admin-content -->
</div><!-- /admin-main -->

</div><!-- /admin-layout -->

<script>
// Sidebar toggle on mobile
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('adminSidebar');
if (toggle) {
  toggle.style.display = 'flex';
  document.addEventListener('click', e => {
    if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });
}

// Confirm deletes
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm || 'Czy na pewno?')) e.preventDefault();
  });
});

// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => el.style.opacity = '0', 4000);
  setTimeout(() => el.remove(),           4400);
});

// Image upload preview
function previewImages(input, previewId) {
  const preview = document.getElementById(previewId);
  if (!preview) return;
  preview.innerHTML = '';
  Array.from(input.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const div = document.createElement('div');
      div.className = 'img-preview-item';
      div.innerHTML = `<img src="${e.target.result}" alt="preview">`;
      preview.appendChild(div);
    };
    reader.readAsDataURL(file);
  });
}
</script>
</body>
</html>
