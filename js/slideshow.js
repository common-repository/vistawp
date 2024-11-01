/**
 * Opens the modal by setting the display property to "block".
 */
function openModal() {
  document.getElementById("myModal").style.display = "block";
}

/**
 * Closes the modal by setting the display property to "none".
 */
function closeModal() {
  document.getElementById("myModal").style.display = "none";
}

/**
 * Loads more content in the vista grid.
 */
function vistaGridLoadMore() {
  var container = document.getElementsByClassName('grid-wrapper')[0];
  var currentHeight = container.offsetHeight;
  var scrollHeight = container.scrollHeight;

  if (currentHeight >= scrollHeight) {
    document.getElementById("vista-grid-load-btn").style.display = "none";
    return;
  } else {
    currentHeight += 400;
    container.style.height = currentHeight + 'px';
  }
}

var vista_slide_index = 1;

/**
 * Shows the next or previous slide.
 * @param {number} n - The number of slides to move (positive for next, negative for previous).
 */
function plusSlides(n) {
  showSlides(vista_slide_index += n);
}

/**
 * Shows the slide with the specified index.
 * @param {number} n - The index of the slide to show.
 */
function currentSlide(n) {
  showSlides(vista_slide_index = n);
}

/**
 * Shows the slides based on the given slide index.
 * @param {number} n - The index of the slide to show.
 */
function showSlides(n) {
  var slides = document.getElementsByClassName('vista-slide-item');
  var slide_number = document.getElementById('vista-slide-number');
  var size = slides.length;

  if (n > size) {
    vista_slide_index = 1;
  }
  
  if (n < 1) {
    vista_slide_index = size;
  }

  for (let i = 0; i < size; i++) {
    slides[i].style.display = "none";
  }
  slides[vista_slide_index - 1].style.display = "block";

  slide_number.innerHTML = "<span>" + vista_slide_index + "/" + size + "</span>";
}

/**
 * Scrolls the vista scroller in the specified direction.
 * @param {string} direction - The direction to scroll ('left' or 'right').
 */
function vista_scroller(direction) {
  if (direction == 'left') {
    document.getElementsByClassName("vista-scroller-photobanner")[0].scrollLeft -= 50;
  } else {
    document.getElementsByClassName("vista-scroller-photobanner")[0].scrollLeft += 50;
  }
}


// Shows the first slide when the page loads
window.addEventListener("load", function() {
  showSlides(1);
});

