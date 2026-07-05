/**
 * site.js
 * Shared front-end behaviors for the public website.
 */
$(function () {
  // Smooth scroll for in-page anchor links
  $('a[href^="#"]').on('click', function (e) {
    const target = $(this.getAttribute('href'));
    if (target.length) {
      e.preventDefault();
      $('html, body').animate({ scrollTop: target.offset().top - 70 }, 400);
    }
  });
});
