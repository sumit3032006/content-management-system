/**
 * admin.js
 * Small shared behaviors for the admin panel (mobile sidebar toggle).
 */
$(function () {
  $('#sidebarToggle').on('click', function () {
    $('#adminSidebar').toggleClass('show');
  });

  // Close sidebar when clicking outside on mobile
  $(document).on('click', function (e) {
    if ($(window).width() < 992) {
      if (!$(e.target).closest('#adminSidebar, #sidebarToggle').length) {
        $('#adminSidebar').removeClass('show');
      }
    }
  });
});
