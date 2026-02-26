
  //code to clear local storage, session storage, and cookies on new deployment
  (function () {
    const SITE_VERSION = "1.0.0"; // 🔁 change on every deployment

    const storedVersion = localStorage.getItem("SITE_VERSION");

    if (storedVersion !== SITE_VERSION) {
      // Clear storage
      localStorage.clear();
      sessionStorage.clear();

      // Clear cookies (non-HttpOnly, current domain)
      document.cookie.split(";").forEach(cookie => {
        document.cookie = cookie
          .replace(/^ +/, "")
          .replace(/=.*/, "=;expires=" + new Date(0).toUTCString() + ";path=/");
      });

      // Save new version
      localStorage.setItem("SITE_VERSION", SITE_VERSION);

      // Reload page
      window.location.reload();
    }
  })();
  
(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.sticky-top').addClass('shadow-sm').css('top', '0px');
        } else {
            $('.sticky-top').removeClass('shadow-sm').css('top', '-100px');
        }
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


  
const slides = document.querySelectorAll('.carousel-slide');
const dots = document.querySelectorAll('.dot');
const nextBtn = document.querySelector('.next');
const prevBtn = document.querySelector('.prev');
let index = 0;
let timer;

/* Show Slide Function */
function showSlide(i) {
  slides.forEach((slide, idx) => slide.classList.toggle('active', idx === i));
  dots.forEach((dot, idx) => dot.classList.toggle('active', idx === i));
}

/* Next/Prev Controls */
function nextSlide() {
  index = (index + 1) % slides.length;
  showSlide(index);
}

function prevSlide() {
  index = (index - 1 + slides.length) % slides.length;
  showSlide(index);
}

/* Auto Slide */
function startAuto() {
  timer = setInterval(nextSlide, 5000);
}
function stopAuto() {
  clearInterval(timer);
}

/* Event Listeners */
nextBtn.addEventListener('click', () => {
  nextSlide();
  stopAuto(); startAuto();
});
prevBtn.addEventListener('click', () => {
  prevSlide();
  stopAuto(); startAuto();
});
dots.forEach((dot, i) => {
  dot.addEventListener('click', () => {
    index = i;
    showSlide(index);
    stopAuto(); startAuto();
  });
});

startAuto();


// ****home page about section start***********
document.addEventListener("mousemove", function(e) {
    document.querySelectorAll(".parallax-card").forEach(card => {
      const speed = card.getAttribute("data-speed");
      const x = (window.innerWidth - e.pageX * speed) / 200;
      const y = (window.innerHeight - e.pageY * speed) / 200;
      card.style.transform = `translateX(${x}px) translateY(${y}px)`;
    });
  });

// ********event secton home page***********
    /* SYNC BOTH CAROUSELS */
    const imgCarousel = document.querySelector('#eventImageCarousel');
    const textCarousel = document.querySelector('#eventTextCarousel');

    imgCarousel.addEventListener('slide.bs.carousel', function (e) {
        const index = e.to;
        const text = bootstrap.Carousel.getInstance(textCarousel);
        text.to(index);
    });
// **********event section home page************
var swiper = new Swiper(".myEqualSwiper", {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    autoplay: {
        delay: 4000,
        disableOnInteraction: false,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
});


// ***************home page animated counter css

  const counters = document.querySelectorAll('.counter');
const speed = 500; // lower = faster

const startCounter = (entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const counter = entry.target;
            const target = +counter.getAttribute('data-target');
            let count = 0;

            const updateCount = () => {
                const increment = target / speed;
                if (count < target) {
                    count += increment;
                    counter.innerText = Math.ceil(count);
                    requestAnimationFrame(updateCount);
                } else {
                    counter.innerText = target.toLocaleString(); // format 50,000
                }
            };

            updateCount();
            observer.unobserve(counter); // run only once
        }
    });
};

const observer = new IntersectionObserver(startCounter, { threshold: 0.4 });

counters.forEach(counter => {
    observer.observe(counter);
});

// *********home page animated counter*****

document.querySelectorAll('.left-slider').forEach(slider => {
  const slides = slider.querySelectorAll('.inner-slide');
  let index = 0;
  const interval = slider.dataset.interval || 3000;
  slides.forEach((s, i) => s.style.display = i === 0 ? 'block' : 'none');
  setInterval(() => {
    slides[index].style.display = 'none';
    index = (index + 1) % slides.length;
    slides[index].style.display = 'block';
  }, interval);
});
 
})(jQuery);

// document.getElementById("contactForm").addEventListener("submit", function (e) {
//     e.preventDefault();

//     const form = e.target;
//     const responseBox = document.getElementById("responseMessage");

//     fetch(form.action, {
//         method: "POST",
//         body: new FormData(form)
//     })
//     .then(res => res.text())
//     .then(msg => {
//         alert(msg);                 // show success alert
//         window.location.href = "http://localhost/new/contact.html";
// // redirect
//     })
//     .catch(() => {
//         alert("Something went wrong. Please try again.");
//     });
// });


// Real-time validation
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');

    // Create validation message elements
    function createValidationMessage(inputId) {
        const input = document.getElementById(inputId);
        const parent = input.closest('.form-floating');
        const msgDiv = document.createElement('div');
        msgDiv.className = 'validation-message';
        msgDiv.style.cssText = 'color: #dc3545; font-size: 0.875rem; margin-top: 0.25rem; display: none;';
        parent.appendChild(msgDiv);
        return msgDiv;
    }

    const nameMsg = createValidationMessage('name');
    const emailMsg = createValidationMessage('email');
    const phoneMsg = createValidationMessage('phone');
    const subjectMsg = createValidationMessage('subject');
    const messageMsg = createValidationMessage('message');

    // Real-time validation functions
    function validateName() {
        const value = nameInput.value.trim();
        const namePattern = /^[A-Za-z\s]{3,}$/;
        
        if (value.length === 0) {
            nameMsg.style.display = 'none';
            nameInput.style.borderColor = '';
            return true;
        }
        
        if (!namePattern.test(value)) {
            nameMsg.textContent = 'Only letters and spaces allowed (min 3 characters)';
            nameMsg.style.display = 'block';
            nameInput.style.borderColor = '#dc3545';
            return false;
        } else {
            nameMsg.style.display = 'none';
            nameInput.style.borderColor = '#28a745';
            return true;
        }
    }

    function validateEmail() {
        const value = emailInput.value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (value.length === 0) {
            emailMsg.style.display = 'none';
            emailInput.style.borderColor = '';
            return true;
        }
        
        if (!emailPattern.test(value)) {
            emailMsg.textContent = 'Please enter a valid email address';
            emailMsg.style.display = 'block';
            emailInput.style.borderColor = '#dc3545';
            return false;
        } else {
            emailMsg.style.display = 'none';
            emailInput.style.borderColor = '#28a745';
            return true;
        }
    }

    function validatePhone() {
        const value = phoneInput.value.trim();
        const phonePattern = /^[6-9]\d{9}$/;
        
        if (value.length === 0) {
            phoneMsg.style.display = 'none';
            phoneInput.style.borderColor = '';
            return true;
        }
        
        if (!phonePattern.test(value)) {
            if (value.length < 10) {
                phoneMsg.textContent = 'Must be exactly 10 digits';
            } else if (!/^[6-9]/.test(value)) {
                phoneMsg.textContent = 'Must start with 6, 7, 8, or 9';
            } else {
                phoneMsg.textContent = 'Only numbers allowed';
            }
            phoneMsg.style.display = 'block';
            phoneInput.style.borderColor = '#dc3545';
            return false;
        } else {
            phoneMsg.style.display = 'none';
            phoneInput.style.borderColor = '#28a745';
            return true;
        }
    }

    function validateSubject() {
        const value = subjectInput.value.trim();
        
        if (value.length === 0) {
            subjectMsg.style.display = 'none';
            subjectInput.style.borderColor = '';
            return true;
        }
        
        if (value.length < 3) {
            subjectMsg.textContent = 'Subject must contain at least 3 characters';
            subjectMsg.style.display = 'block';
            subjectInput.style.borderColor = '#dc3545';
            return false;
        } else {
            subjectMsg.style.display = 'none';
            subjectInput.style.borderColor = '#28a745';
            return true;
        }
    }

    function validateMessage() {
        const value = messageInput.value.trim();
        
        if (value.length === 0) {
            messageMsg.style.display = 'none';
            messageInput.style.borderColor = '';
            return true;
        }
        
        if (value.length < 5) {
            messageMsg.textContent = 'Message must contain at least 5 characters';
            messageMsg.style.display = 'block';
            messageInput.style.borderColor = '#dc3545';
            return false;
        } else {
            messageMsg.style.display = 'none';
            messageInput.style.borderColor = '#28a745';
            return true;
        }
    }

    // Add event listeners for real-time validation with input prevention
    nameInput.addEventListener('input', function(e) {
        const value = e.target.value;
        // Remove any characters that are not letters or spaces
        e.target.value = value.replace(/[^A-Za-z\s]/g, '');
        validateName();
    });
    nameInput.addEventListener('blur', validateName);
    
    // Prevent typing invalid characters in name field
    nameInput.addEventListener('keypress', function(e) {
        const char = String.fromCharCode(e.which || e.keyCode);
        if (!/[A-Za-z\s]/.test(char)) {
            e.preventDefault();
        }
    });
    
    emailInput.addEventListener('input', validateEmail);
    emailInput.addEventListener('blur', validateEmail);
    
    phoneInput.addEventListener('input', function(e) {
        const value = e.target.value;
        // Remove any characters that are not numbers
        e.target.value = value.replace(/[^0-9]/g, '');
        // Limit to 10 digits
        if (e.target.value.length > 10) {
            e.target.value = e.target.value.slice(0, 10);
        }
        validatePhone();
    });
    phoneInput.addEventListener('blur', validatePhone);
    
    // Prevent typing non-numeric characters in phone field
    phoneInput.addEventListener('keypress', function(e) {
        const char = String.fromCharCode(e.which || e.keyCode);
        if (!/[0-9]/.test(char)) {
            e.preventDefault();
        }
    });
    
    subjectInput.addEventListener('input', validateSubject);
    subjectInput.addEventListener('blur', validateSubject);
    
    messageInput.addEventListener('input', validateMessage);
    messageInput.addEventListener('blur', validateMessage);
});

document.getElementById("contactForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const form = e.target;
    
    // Get all validation functions from the scope
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');
    
    // Trigger validation on all fields
    const nameValid = /^[A-Za-z\s]{3,}$/.test(nameInput.value.trim());
    const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());
    const phoneValid = /^[6-9]\d{9}$/.test(phoneInput.value.trim());
    const subjectValid = subjectInput.value.trim().length >= 3;
    const messageValid = messageInput.value.trim().length >= 5;
    
    // Check if all validations pass
    if (!nameValid || !emailValid || !phoneValid || !subjectValid || !messageValid) {
        alert('Please fix all validation errors before submitting.');
        return;
    }

    // reCAPTCHA validation
    const token = document.querySelector('textarea#g-recaptcha-response')?.value || '';
    if (!token.trim()) {
        alert('Please complete the reCAPTCHA.');
        return;
    }

    fetch(form.action, {
        method: "POST",
        body: new FormData(form)
    })
    .then(res => res.text())
    .then(msg => {
        alert(msg);
        form.reset();
        // Reset reCAPTCHA
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.reset();
        }
        // Reset validation styles
        document.querySelectorAll('.validation-message').forEach(msg => msg.style.display = 'none');
        document.querySelectorAll('.form-control').forEach(input => input.style.borderColor = '');
    })
    .catch(() => {
        alert("Something went wrong. Please try again.");
    });
});



