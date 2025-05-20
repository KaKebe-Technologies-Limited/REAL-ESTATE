// Function to show profile content
function showProfile() {
    console.log('showProfile function called');
    // Hide all content sections
    document.querySelectorAll('#main-content > div').forEach(div => {
        div.style.display = 'none';
    });

    // Show profile content
    const profileContent = document.getElementById('profile-content');
    if (profileContent) {
        profileContent.style.display = 'block';
        console.log('Profile content displayed');

        // Load user profile data
        if (typeof loadUserProfile === 'function') {
            loadUserProfile();
            console.log('loadUserProfile function called');
        } else {
            console.error('loadUserProfile function not found');
        }
    } else {
        console.error('Profile content element not found');
    }
}

// Function to show dashboard content
function showDashboard() {
    console.log('showDashboard function called');
    // Hide all content sections
    document.querySelectorAll('#main-content > div').forEach(div => {
        div.style.display = 'none';
    });

    // Show dashboard content
    const dashboardContent = document.getElementById('dashboard-content');
    if (dashboardContent) {
        dashboardContent.style.display = 'block';
        console.log('Dashboard content displayed');
    } else {
        console.error('Dashboard content element not found');
    }
}

// Function to show settings content
function showSettings() {
    console.log('showSettings function called');
    // Hide all content sections
    document.querySelectorAll('#main-content > div').forEach(div => {
        div.style.display = 'none';
    });

    // Show settings content
    const settingsContent = document.getElementById('settings-content');
    if (settingsContent) {
        settingsContent.style.display = 'block';
        console.log('Settings content displayed');
    } else {
        console.error('Settings content element not found');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded event fired in manager-dashboard-new.js');

    // Get elements
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    // Check if elements exist before using them
    if (sidebarToggle && sidebar && mainContent) {
        // Toggle function
        function toggleSidebar(e) {
            if (e) e.preventDefault();
            sidebar.classList.toggle('collapsed');

            // Only adjust main content on desktop
            if (window.innerWidth > 768) {
                mainContent.classList.toggle('expanded');
            }
        }

        // Add click event listener
        sidebarToggle.addEventListener('click', toggleSidebar);

        // Handle responsive behavior
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.remove('expanded');
            }
        });
    } else {
        console.error('Required elements not found for sidebar toggle');
    }

    // Theme switcher
    const themeSwitcher = document.getElementById('theme-switcher');
    if (themeSwitcher) {
        themeSwitcher.addEventListener('click', () => {
            const currentTheme = document.body.classList.contains('dark-theme') ? 'dark' : 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            setTheme(newTheme);
        });
    }

    // Set theme function
    function setTheme(theme, init = false) {
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            localStorage.setItem('preferred-theme', 'dark');
        } else {
            document.body.classList.remove('dark-theme');
            localStorage.setItem('preferred-theme', 'light');
        }

        // Update theme switcher icon if it exists
        const themeIcon = document.querySelector('#theme-switcher i');
        if (themeIcon) {
            if (theme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        }
    }

    // Initialize theme
    const savedTheme = localStorage.getItem('preferred-theme') || 'light';
    setTheme(savedTheme, true);

    // Profile form toggle
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const saveProfileBtn = document.getElementById('save-profile-btn');
    const cancelProfileBtn = document.getElementById('cancel-profile-btn');
    const profileView = document.getElementById('profile-view');
    const profileForm = document.getElementById('profile-form');

    if (editProfileBtn && saveProfileBtn && cancelProfileBtn && profileView && profileForm) {
        editProfileBtn.addEventListener('click', function() {
            profileView.style.display = 'none';
            profileForm.style.display = 'block';
        });

        cancelProfileBtn.addEventListener('click', function() {
            profileForm.style.display = 'none';
            profileView.style.display = 'block';
        });
    }

    // Profile picture change
    const profilePictureInput = document.getElementById('profile-picture-input');
    const changePictureBtn = document.getElementById('change-picture-btn');
    const profilePicturePreview = document.getElementById('profile-picture-preview');

    if (profilePictureInput && changePictureBtn && profilePicturePreview) {
        changePictureBtn.addEventListener('click', function() {
            profilePictureInput.click();
        });

        profilePictureInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePicturePreview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        // Property Type Distribution Chart
        const propertyTypeCtx = document.getElementById('propertyTypeChart');
        if (propertyTypeCtx) {
            new Chart(propertyTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Apartments', 'Houses', 'Commercial', 'Land'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        // Monthly Revenue Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    datasets: [{
                        label: "Revenue",
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: [0, 10000, 5000, 15000, 10000, 20000, 15000, 25000, 20000, 30000, 25000, 40000],
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        },
                        y: {
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                callback: function(value) {
                                    return 'UGX ' + value;
                                }
                            },
                            grid: {
                                color: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        },
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyColor: "#858796",
                            titleMarginBottom: 10,
                            titleColor: '#6e707e',
                            titleFontSize: 14,
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            padding: 15,
                            displayColors: false,
                            intersect: false,
                            mode: 'index',
                            caretPadding: 10,
                            callbacks: {
                                label: function(context) {
                                    var label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'UGX ' + context.parsed.y;
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Income Chart
        const incomeChartCtx = document.getElementById('incomeChart');
        let incomeChart;
        if (incomeChartCtx) {
            incomeChart = new Chart(incomeChartCtx, {
                type: 'bar',
                data: {
                    labels: ['Loading...'],
                    datasets: [
                        {
                            label: 'Rental Income',
                            backgroundColor: '#007bff',
                            borderColor: '#0056b3',
                            borderWidth: 1,
                            data: [0]
                        },
                        {
                            label: 'Sales Income',
                            backgroundColor: '#dc3545',
                            borderColor: '#a71d2a',
                            borderWidth: 1,
                            data: [0]
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'UGX ' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += 'UGX ' + context.parsed.y.toLocaleString();
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Property Distribution Chart
        const distributionChartCtx = document.getElementById('distributionChart');
        let distributionChart;
        if (distributionChartCtx) {
            distributionChart = new Chart(distributionChartCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Rental Properties', 'Sales Properties'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: ['#007bff', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Fetch income data
        fetchIncomeData();

        // Add event listener for period selector
        const incomePeriodSelector = document.getElementById('income-period-selector');
        if (incomePeriodSelector) {
            incomePeriodSelector.addEventListener('change', function() {
                fetchIncomeData(this.value);
            });
        }

        // Function to fetch income data
        function fetchIncomeData(period = '6months') {
            fetch(`get_manager_income.php?period=${period}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update income summary cards
                        updateIncomeSummary(data);

                        // Update income chart
                        updateIncomeChart(data, incomeChart);

                        // Update distribution chart
                        updateDistributionChart(data, distributionChart);
                    } else {
                        console.error('Error fetching income data:', data.message);
                        showCustomAlert('error', 'Error', 'Failed to load income data. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showCustomAlert('error', 'Error', 'Failed to load income data. Please try again.');
                });
        }

        // Function to update income summary
        function updateIncomeSummary(data) {
            // Format currency
            const formatCurrency = (amount) => {
                return 'UGX ' + amount.toLocaleString();
            };

            // Update summary cards
            document.getElementById('total-income').textContent = formatCurrency(data.total_income);
            document.getElementById('rental-income').textContent = formatCurrency(data.rental_income);
            document.getElementById('sales-income').textContent = formatCurrency(data.sales_income);

            // Update trends (using placeholder values for now)
            document.getElementById('income-trend').textContent = '15% from last month';
            document.getElementById('rental-trend').textContent = '12% from last month';
            document.getElementById('sales-trend').textContent = '18% from last month';
        }

        // Function to update income chart
        function updateIncomeChart(data, chart) {
            if (chart && data.monthly_data) {
                chart.data.labels = data.monthly_data.months;
                chart.data.datasets[0].data = data.monthly_data.rental;
                chart.data.datasets[1].data = data.monthly_data.sales;
                chart.update();
            }
        }

        // Function to update distribution chart
        function updateDistributionChart(data, chart) {
            if (chart && data.property_distribution) {
                chart.data.datasets[0].data = [
                    data.property_distribution.rental,
                    data.property_distribution.sales
                ];
                chart.update();
            }
        }
    }

    // Function to show income details
    window.showIncomeDetails = function() {
        // This function would show a detailed income breakdown
        // For now, just show an alert
        showCustomAlert('info', 'Income Details', 'Detailed income breakdown will be available in a future update.');
    };
});
