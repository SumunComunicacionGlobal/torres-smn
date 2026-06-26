document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".wp-block-smn-tabs").forEach((tabContainer) => {
        const initTabs = (container) => {
            const tabs = container.querySelectorAll('[role="tab"]');
            const keyCodes = { 37: -1, 38: -1, 39: 1, 40: 1 };
            let currentTab = 0;

            const showTab = (tabId) => {
                container.querySelectorAll('div[tabid]:not([role="tab"])').forEach((content) => {
                    //content.style.display = "none";
                    content.classList.remove("active");
                });

                tabs.forEach((tab) => {
                    tab.classList.remove("active");
                    tab.setAttribute("aria-selected", "false");
                });

                currentTab = parseInt(tabId);
                const activeTab = container.querySelector(`[role="tab"][tabid="${currentTab}"]`);
                activeTab.classList.add("active");
                activeTab.setAttribute("aria-selected", "true");

                const activeContent = container.querySelector(`div[tabid="${currentTab}"]:not([role="tab"])`);
                //activeContent.style.display = "block";
                activeContent.classList.add("active");

                const tabChangedEvent = new CustomEvent("tabChanged", { detail: activeTab });
                window.dispatchEvent(tabChangedEvent);
            };

            const handleKeydown = (event) => {
                const keyCode = event.keyCode;
                let isArrowKey = false;

                if (container.classList.contains("is-vertical")) {
                    if (keyCode === 38 || keyCode === 40) {
                        event.preventDefault();
                        isArrowKey = true;
                    }
                } else {
                    if (keyCode === 37 || keyCode === 39) {
                        isArrowKey = true;
                    }
                }

                if (isArrowKey) {
                    const newTab = currentTab + keyCodes[keyCode];
                    if (tabs[newTab]) {
                        tabs[newTab].focus();
                    } else if (keyCode === 37 || keyCode === 38) {
                        tabs[tabs.length - 1].focus();
                    } else if (keyCode === 39 || keyCode === 40) {
                        tabs[0].focus();
                    }
                }
            };

            const handleFocus = (event) => {
                const target = event.target;
                setTimeout(() => {
                    if (target === document.activeElement) {
                        showTab(target.getAttribute("tabid"));
                    }
                }, 0);
            };

            currentTab = container.getAttribute("data-default-tab") || 0;
            showTab(currentTab);

            tabs.forEach((tab) => {
                tab.addEventListener("click", () => {
                    showTab(tab.getAttribute("tabid"));
                });

                tab.addEventListener("keydown", (event) => {
                    if (event.keyCode === 38 || event.keyCode === 40) {
                        handleKeydown(event);
                    }
                });

                tab.addEventListener("keyup", (event) => {
                    if (event.keyCode === 37 || event.keyCode === 39) {
                        handleKeydown(event);
                    }
                });

                tab.addEventListener("focus", handleFocus);
            });
        };

        initTabs(tabContainer);
    });
});