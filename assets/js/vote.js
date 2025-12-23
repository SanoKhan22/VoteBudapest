/**
 * AJAX Voting Handler
 * EXTRA TASK: Vote without page reload (1.0 pt)
 */

document.addEventListener('DOMContentLoaded', function() {
    // Handle all vote form submissions
    const voteForms = document.querySelectorAll('.vote-form');
    
    voteForms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const button = form.querySelector('button[type="submit"]');
            const projectId = formData.get('project_id');
            const voteCountSpan = document.querySelector(`.vote-count-${projectId}`);
            
            // Disable button during request
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '⏳ Processing...';
            
            try {
                const response = await fetch('/actions/vote-action.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update vote count
                    if (voteCountSpan) {
                        voteCountSpan.textContent = data.vote_count;
                    }
                    
                    // Update button based on vote status
                    const actionInput = form.querySelector('input[name="action"]');
                    if (data.user_voted) {
                        // User just voted - update to "Voted" state
                        button.classList.remove('btn-outline-success', 'btn-primary', 'btn-outline-primary');
                        button.classList.add('btn-success');
                        button.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Voted';
                        if (actionInput) actionInput.value = 'unvote';
                    } else {
                        // User removed vote - update to "Vote" state
                        button.classList.remove('btn-success', 'btn-outline-success');
                        button.classList.add('btn-primary');
                        button.innerHTML = '<i class="bi bi-hand-thumbs-up-fill me-1"></i> Vote';
                        if (actionInput) actionInput.value = 'vote';
                    }
                    
                    // Update remaining votes display
                    updateRemainingVotes(data.category_id, data.remaining_votes);
                    
                    // Update progress bar if on index page
                    updateVoteProgress(data.category_id, data.remaining_votes);
                    
                    // Show success message
                    showNotification(data.message, 'success');
                } else {
                    // Show error message
                    showNotification(data.message || 'An error occurred', 'error');
                    button.innerHTML = originalText;
                }
                
                button.disabled = false;
                
            } catch (error) {
                console.error('Vote error:', error);
                showNotification('An error occurred. Please try again.', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
    });
});

/**
 * Update remaining votes counter
 */
function updateRemainingVotes(categoryId, remaining) {
    // Update any remaining votes display on the page
    const remainingElements = document.querySelectorAll(`[data-category="${categoryId}"] .remaining-votes`);
    remainingElements.forEach(el => {
        el.textContent = remaining;
    });
}

/**
 * Update vote progress bar
 */
function updateVoteProgress(categoryId, remaining) {
    const categorySection = document.querySelector(`[data-category="${categoryId}"]`);
    if (!categorySection) return;
    
    const progressBar = categorySection.querySelector('.progress-bar');
    if (!progressBar) return;
    
    const maxVotes = 3; // MAX_VOTES_PER_CATEGORY
    const votesUsed = maxVotes - remaining;
    const percentage = (votesUsed / maxVotes) * 100;
    
    progressBar.style.width = percentage + '%';
    progressBar.setAttribute('aria-valuenow', votesUsed);
    
    // Update progress bar color
    if (remaining === 0) {
        progressBar.classList.remove('bg-success');
        progressBar.classList.add('bg-danger');
    } else {
        progressBar.classList.remove('bg-danger');
        progressBar.classList.add('bg-success');
    }
    
    // Update "X used" text
    const usedText = categorySection.querySelector('.voting-progress small:last-child');
    if (usedText) {
        usedText.textContent = votesUsed + ' used';
    }
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.setAttribute('role', 'alert');
    
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 150);
    }, 3000);
}
