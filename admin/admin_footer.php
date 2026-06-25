<?php
// admin/admin_footer.php
// Shared premium administrative footer closing layout
?>
  </main>
</div>

<!-- Global Tooltip Element -->
<div id="global-tooltip"></div>
<script>
  document.addEventListener('mouseover', function(e) {
    let target = e.target.closest('.custom-tooltip-trigger');
    if(target) {
       let tooltip = document.getElementById('global-tooltip');
       tooltip.innerText = target.getAttribute('data-tooltip');
       tooltip.style.display = 'block';
       let rect = target.getBoundingClientRect();
       tooltip.style.left = (rect.left + window.scrollX + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
       tooltip.style.top = (rect.bottom + window.scrollY + 8) + 'px';
    }
  });
  document.addEventListener('mouseout', function(e) {
    if(e.target.closest('.custom-tooltip-trigger')) {
       document.getElementById('global-tooltip').style.display = 'none';
    }
  });
</script>

</body>
</html>
