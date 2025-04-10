function viewAllActivities() {
    // Hide other content sections
    document.querySelectorAll('#main-content > div').forEach(div => {
        div.style.display = 'none';
    });

    // Show activities section
    const activitiesContent = document.getElementById('activities-content');
    if (activitiesContent) {
        activitiesContent.style.display = 'block';
        loadAllActivities();
    }
}

function loadAllActivities() {
    fetch('get_all_activities.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update activities list with all activities
                const activitiesList = document.querySelector('#activities-content .activity-list');
                if (activitiesList) {
                    activitiesList.innerHTML = data.activities.map(activity => `
                        <div class="activity-item">
                            <div class="activity-icon ${activity.icon_bg_class}">
                                <i class="${activity.icon_class}"></i>
                            </div>
                            <div class="activity-content">
                                <h4>${activity.title}</h4>
                                <p>${activity.description}</p>
                                <span class="activity-time">${activity.time_ago}</span>
                            </div>
                            ${activity.status === 'new' ? `
                                <div class="activity-status">
                                    <span class="badge bg-success">New</span>
                                </div>
                            ` : ''}
                        </div>
                    `).join('');
                }
            }
        })
        .catch(error => console.error('Error loading activities:', error));
}