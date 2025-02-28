// Main JavaScript file for ArtiSell

document.addEventListener("DOMContentLoaded", function () {
  // Initialize category slider functionality
  initCategorySlider();

  // Initialize quantity controls for product detail page
  initQuantityControls();

  // Initialize image gallery for product detail page
  initProductGallery();

  // Initialize mobile menu toggle
  initMobileMenu();
});

// Category slider functionality
function initCategorySlider() {
  const wrapper = document.querySelector(".category-wrapper");
  const prevBtn = document.getElementById("category-prev");
  const nextBtn = document.getElementById("category-next");

  if (wrapper && prevBtn && nextBtn) {
    const cards = document.querySelectorAll(".category-card");
    const cardWidth = 220; // card width + margin
    let position = 0;

    // Calculate visible cards based on container width
    const calculateVisibleCards = () => {
      return Math.floor(wrapper.offsetWidth / cardWidth);
    };

    let visibleCards = calculateVisibleCards();

    // Update on window resize
    window.addEventListener("resize", function () {
      visibleCards = calculateVisibleCards();
    });

    // Previous button click
    prevBtn.addEventListener("click", function () {
      if (position > 0) {
        position--;
        updateSliderPosition();
      }
    });

    // Next button click
    nextBtn.addEventListener("click", function () {
      if (position < cards.length - visibleCards) {
        position++;
        updateSliderPosition();
      }
    });

    // Update slider position
    function updateSliderPosition() {
      wrapper.style.transform = `translateX(-${position * cardWidth}px)`;

      // Update button states
      prevBtn.disabled = position === 0;
      nextBtn.disabled = position >= cards.length - visibleCards;
    }

    // Initial button states
    prevBtn.disabled = position === 0;
    nextBtn.disabled = cards.length <= visibleCards;
  }
}

// Quantity controls for product detail page
function initQuantityControls() {
  const quantityInput = document.getElementById("quantity");
  const decreaseBtn = document.querySelector(".quantity-decrease");
  const increaseBtn = document.querySelector(".quantity-increase");

  if (quantityInput && decreaseBtn && increaseBtn) {
    decreaseBtn.addEventListener("click", function () {
      const currentValue = parseInt(quantityInput.value);
      if (currentValue > 1) {
        quantityInput.value = currentValue - 1;
      }
    });

    increaseBtn.addEventListener("click", function () {
      const currentValue = parseInt(quantityInput.value);
      quantityInput.value = currentValue + 1;
    });

    quantityInput.addEventListener("change", function () {
      const value = parseInt(this.value);
      if (isNaN(value) || value < 1) {
        this.value = 1;
      }
    });
  }
}

// Product image gallery
function initProductGallery() {
  const mainImage = document.getElementById("main-product-image");
  const thumbnails = document.querySelectorAll(".product-image-thumbs img");

  if (mainImage && thumbnails.length > 0) {
    thumbnails.forEach((thumb) => {
      thumb.addEventListener("click", function () {
        // Update main image
        mainImage.src = this.src;

        // Update active thumbnail
        thumbnails.forEach((t) => t.classList.remove("border-primary"));
        this.classList.add("border-primary");
      });
    });
  }
}

// Mobile menu toggle
function initMobileMenu() {
  const menuToggle = document.querySelector(".navbar-toggler");
  const mobileMenu = document.querySelector(".navbar-collapse");

  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener("click", function () {
      mobileMenu.classList.toggle("show");
    });
  }
}

// Cart quantity update via AJAX
function updateCartQuantity(itemId, quantity) {
  // Ensure quantity is at least 1
  quantity = Math.max(1, parseInt(quantity));

  // Update input field
  const quantityInput = document.getElementById("quantity-" + itemId);
  if (quantityInput) {
    quantityInput.value = quantity;
  }

  // Send AJAX request
  fetch(
    `index.php?page=cart&action=update&id=${itemId}&quantity=${quantity}&ajax=1`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update item subtotal
        const subtotalElement = document.getElementById("subtotal-" + itemId);
        if (subtotalElement) {
          subtotalElement.textContent = "₱" + formatNumber(data.item_subtotal);
        }

        // Update cart totals
        const subtotalElement = document.getElementById("cart-subtotal");
        const taxElement = document.getElementById("cart-tax");
        const totalElement = document.getElementById("cart-total");

        if (subtotalElement)
          subtotalElement.textContent = "₱" + formatNumber(data.subtotal);
        if (taxElement) taxElement.textContent = "₱" + formatNumber(data.tax);
        if (totalElement)
          totalElement.textContent = "₱" + formatNumber(data.total);
      }
    })
    .catch((error) => console.error("Error updating cart:", error));
}

// Format number with commas and decimal places
function formatNumber(number) {
  return new Intl.NumberFormat("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(number);
}
