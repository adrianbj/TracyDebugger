addFilterBox({
    suffix: "-userswitcher-panel",
    target: {
        selector: "#tracy-debug-panel-ProcessWire-UserSwitcherPanel select[name='userSwitcher']",
        items: "option"
    },
    extraFilterAttrs: [
        'data-label'
    ],
    wrapper: {
        attrs: {
            id: "tracyUserSwitcherFilterBoxWrap",
            class: "tracy-filterbox-wrap tracy-filterbox-titlebar-wrap"
        }
    },
    input: {
        attrs: {
            placeholder: "Find user..."
        }
    },
    addTo: {
        selector: "#tracy-debug-panel-ProcessWire-UserSwitcherPanel .tracy-icons",
        position: "before"
    },
    displays: setupTracyPanelFilterBoxDisplays
});
