// Modern Loader for Real Estate Website

document.addEventListener('DOMContentLoaded', function() {
    // Create loader elements
    const loaderContainer = document.createElement('div');
    loaderContainer.className = 'loader-container';
    
    const loader = document.createElement('div');
    loader.className = 'loader';
    
    const houseLoader = document.createElement('div');
    houseLoader.className = 'house-loader';
    
    const roof = document.createElement('div');
    roof.className = 'roof';
    
    const building = document.createElement('div');
    building.className = 'building';
    
    const door = document.createElement('div');
    door.className = 'door';
    
    const window1 = document.createElement('div');
    window1.className = 'window';
    
    const window2 = document.createElement('div');
    window2.className = 'window';
    
    const loadingText = document.createElement('div');
    loadingText.className = 'loading-text';
    loadingText.textContent = 'LOADING';
    
    // Append elements
    houseLoader.appendChild(roof);
    houseLoader.appendChild(building);
    houseLoader.appendChild(door);
    houseLoader.appendChild(window1);
    houseLoader.appendChild(window2);
    
    loader.appendChild(houseLoader);
    loader.appendChild(loadingText);
    
    loaderContainer.appendChild(loader);
    
    // Add loader to the body
    document.body.appendChild(loaderContainer);
    
    // Hide loader when page is fully loaded
    window.addEventListener('load', function() {
        setTimeout(function() {
            loaderContainer.classList.add('hidden');
            
            // Remove loader from DOM after transition
            setTimeout(function() {
                document.body.removeChild(loaderContainer);
            }, 500);
        }, 500); // Delay to ensure animations are visible
    });
    
    // Fallback: Hide loader after 5 seconds in case of slow loading resources
    setTimeout(function() {
        if (document.body.contains(loaderContainer)) {
            loaderContainer.classList.add('hidden');
            
            setTimeout(function() {
                if (document.body.contains(loaderContainer)) {
                    document.body.removeChild(loaderContainer);
                }
            }, 500);
        }
    }, 5000);
});
