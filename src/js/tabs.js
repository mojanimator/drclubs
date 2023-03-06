export const
    makeTabs = () => {
        let tabs = document.querySelectorAll("ul.dc-nav-tabs > li");

        for (let i = 0; i < tabs.length; i++) {
            tabs[i].addEventListener("click", switchTab);
        }

        function switchTab(event) {
            event.preventDefault();
            let activeTab = document.querySelector("ul.dc-nav-tabs li.active");
            if (activeTab)
                activeTab.classList.remove("active");
            let activePane = document.querySelector(".dc-tab-pane.active");
            if (activePane)
                document.querySelector(".dc-tab-pane.active").classList.remove("active");

            let clickedTab = event.currentTarget;
            let anchor = event.target;
            let activeID = anchor.getAttribute("href");

            clickedTab.classList.add("active");
            let activeId = document.querySelector(activeID);
            if (activeId)
                activeId.classList.add("active");
        }
    };
