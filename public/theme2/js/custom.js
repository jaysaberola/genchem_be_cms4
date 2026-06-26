(function () {
  function initCarousels() {
    document.querySelectorAll(".cms-carousel").forEach((carousel) => {
      if (carousel.dataset.initialized) return;
      carousel.dataset.initialized = "true";

      const slides = Array.from(carousel.children);
      const track = document.createElement("div");
      track.className = "cms-track";

      slides.forEach(slide => track.appendChild(slide));
      carousel.appendChild(track);

      let index = 0;
      setInterval(() => {
        index = (index + 1) % slides.length;
        track.style.transform = `translateX(-${index * 100}%)`;
      }, 3000);
    });
  }

  initCarousels();
})();
