/* script.js */

document.addEventListener('DOMContentLoaded', function() {
    
    // Navbar dropdown menu logic
    const userBtn = document.querySelector('.user-btn');
    const dropdown = document.getElementById("myDropdown");

    if (userBtn && dropdown) {
        // Toggle menu on click
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle("show");
        });

        // Close menu when clicking outside
        window.addEventListener('click', function(e) {
            if (!userBtn.contains(e.target)) {
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
    }

    // Register page role selection
    const roleSelect = document.getElementById("roleSelect");
    const skillsDiv = document.getElementById("skillsField");

    if (roleSelect && skillsDiv) {
        // Show skills options only for tutors
        roleSelect.addEventListener('change', function() {
            if (this.value === 'tutor') {
                skillsDiv.style.display = "block";
            } else {
                skillsDiv.style.display = "none";
                // Clear checkboxes if not tutor
                const checkboxes = skillsDiv.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(function(cb) { cb.checked = false; });
            }
        });
    }

});

// AJAX rating and review submission
document.addEventListener('submit', function(e) {
    
    // Handle forms with rating-form class
    if (e.target && e.target.classList.contains('rating-form')) {
        e.preventDefault(); // Stop page reload

        const form = e.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button');
        const originalText = submitBtn.innerText;

        // Disable button while loading
        submitBtn.disabled = true;
        submitBtn.innerText = "Saving...";

        // Send data to server
        fetch('rate_lesson.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Get form values
                const ratingVal = formData.get('rating');
                const reviewVal = formData.get('review');
                const stars = '★'.repeat(ratingVal);
                
                // Format review text if present
                let reviewHtml = '';
                if (reviewVal && reviewVal.trim() !== '') {
                    // Clean text for security
                    const safeReview = reviewVal.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    
                    reviewHtml = `
                        <div style="color:#ccc; font-size:0.9em; font-style:italic; margin-top:8px; border-left:2px solid #555; padding-left:10px;">
                            "${safeReview}"
                        </div>
                    `;
                }

                // Create success message HTML
                const successHtml = `
                    <div style="margin-top:10px; padding:15px; background:rgba(255,255,255,0.05); border-radius:8px; animation: fadeIn 0.5s;">
                        <div style="color:#f39c12; font-weight:bold; font-size:1.1em;">You rated: ${stars}</div>
                        ${reviewHtml}
                        <div style="color:#2ecc71; font-size:0.8em; margin-top:8px; text-align:right; font-weight:bold;">Saved successfully!</div>
                    </div>
                `;
                
                // Replace form with message
                form.outerHTML = successHtml; 
                
            } else {
                alert("Error: " + data.message);
                submitBtn.disabled = false;
                submitBtn.innerText = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("An error occurred!");
            submitBtn.disabled = false;
            submitBtn.innerText = originalText;
        });
    }
});