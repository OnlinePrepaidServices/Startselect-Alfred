import fuzzysort from "fuzzysort";

export default {
    data() {
        return {
            action: {
                active: false,
                extendedPhrase: false,
                realtime: false,
                realtimeShouldDeactivate: false,
                timer: null,
                timeout: 1200,
                trigger: null,
            },
            alfred: {
                closePrevention: false,
                doubleShift: false,
                initiated: false,
                initiatedGlobally: false,
                initiating: true,
                loaded: false,
                loading: false,
                phrase: '',
                phraseOverridePrevention: false,
                placeholder: '',
                prefix: null,
                prefixed: false,
                title: '',
                triggered: null,
                visible: false,
            },
            items: {
                current: [],
                filtered: [],
                saved: [],
            },
            messages: {
                current: [],
                timeout: 2200,
            },
            snippets: {
                timer: null,
                timeout: 1000,
            },
        };
    },

    watch: {
        'alfred.phrase': function (value, oldValue) {
            if (value !== oldValue) {
                // Handle the prefix
                let keywords = this.alfred.phrase.split(' '),
                    prefix = keywords.length > 1 ? keywords[0] : null;

                if (this.alfred.prefixed && (!prefix || this.alfred.prefix !== prefix)) {
                    this.previousState(false);

                    this.alfred.prefixed = false;
                }

                this.alfred.prefix = prefix;

                // Handle item filtering
                if (!this.alfred.loading && !this.action.active) {
                    this.filterItems();

                    // Handle prefixed items
                    let prefixedItem = this.getItemByPrefix();

                    if (prefixedItem) {
                        this.triggerPrefixedItem(prefixedItem);
                    }

                    return;
                }

                // Handle action's item filtering when realtime
                if (this.action.active && (this.action.realtime || this.alfred.prefixed)) {
                    // Do we have a timer active?
                    if (this.action.timer) {
                        clearTimeout(this.action.timer);
                    }

                    // Start timer to trigger the action
                    this.action.timer = setTimeout(() => {
                        this.triggerAction(null);
                    }, this.action.timeout);
                }
            }
        },
        'alfred.visible': function (visible) {
            if (visible) {
                this.bindEvents();

                this.$nextTick(() => {
                    this.$refs.phraseInput.focus();
                });
            } else {
                this.unbindEvents();
            }
        },
        'alfred.loading': function () {
            if (this.alfred.visible) {
                this.$nextTick(() => {
                    this.$refs.phraseInput.focus();
                });
            }
        },
        'action.active': function () {
            if (this.alfred.visible) {
                this.$nextTick(() => {
                    this.$refs.phraseInput.focus();
                });
            }
        },
        'items.saved': function () {
            if (this.alfred.visible) {
                this.$nextTick(() => {
                    this.$refs.phraseInput.focus();
                });
            }
        },
    },

    mounted() {
        this.initiateAlfred();

        /**
         * Listen to global Alfred workflow step triggers.
         */
        this.$root.$alfred.listener.$on(`alfredTriggerWorkflowStep`, (workflowStep) => {
            this.handleGlobalWorkflowStepTrigger(workflowStep);
        });

        /**
         * Listen to global Alfred warned workflow step triggers.
         */
        this.$root.$alfred.listener.$on(`alfredTriggerWarnedWorkflowStep`, (workflowStep) => {
            this.$swal({
                title: 'Are you sure you want to do this?',
                icon: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonColor: '#CC3E29',
                onOpen: () => {
                    this.alfred.closePrevention = true;
                },
                onAfterClose: () => {
                    this.alfred.closePrevention = false;
                }
            }).then((result) => {
                if (result.value || null) {
                    this.handleGlobalWorkflowStepTrigger(workflowStep);
                }
            });
        });
    },

    methods: {
        /**
         * Initiate Alfred.
         */
        initiateAlfred() {
            this.$http.post('/alfred/initiate', {
                page: this.getPageData(),
            }).then(response => {
                if (response.status === 200) {
                    this.initiatedAlfred(response.body);
                }
            });
        },

        /**
         * Initiated Alfred.
         *
         * @param {Object} initiateResponse
         */
        initiatedAlfred(initiateResponse) {
            this.alfred.initiated = true;

            this.handleWorkflowStepResponse(initiateResponse);

            // Alfred is ready
            document.addEventListener('keyup', this.triggerAlfredKeyboardEvent);
            document.addEventListener('keyup', this.triggerSnippetKeyboardEvent);
            document.addEventListener('keydown', this.triggerShortcutKeyboardEvent);
        },

        /**
         * Open Alfred.
         */
        openAlfred() {
            if (!this.alfred.visible) {
                this.alfred.visible = true;
            }
        },

        /**
         * Close Alfred.
         */
        closeAlfred() {
            if (this.alfred.visible) {
                // Reset Alfred
                this.alfred.phrase = '';
                this.alfred.visible = false;
            }
        },

        /**
         * Reset Alfred.
         */
        resetAlfred() {
            // Reset states
            this.previousState(true);

            // Reset possible global trigger
            this.alfred.initiatedGlobally = false;
            this.alfred.closePrevention = false;

            // Close Alfred and re-filter so everything is reset
            this.closeAlfred();
            this.filterItems();
        },

        /**
         * Bind events for Alfred.
         */
        bindEvents() {
            document.addEventListener('click', this.triggerAlfredMouseEvent);
            document.addEventListener('keydown', this.triggerItemKeyboardEvent);
        },

        /**
         * Unbind events for Alfred.
         */
        unbindEvents() {
            document.removeEventListener('click', this.triggerAlfredMouseEvent);
            document.removeEventListener('keydown', this.triggerItemKeyboardEvent);
        },

        /**
         * Trigger Alfred's keyboard event.
         *
         * @param {KeyboardEvent} event
         */
        triggerAlfredKeyboardEvent(event) {
            // Ignore when Alfred is already visible
            if (this.alfred.visible) {
                return;
            }

            if (event.key === '.') {
                // Skip when target of event is in an input element
                if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
                    return;
                }

                event.preventDefault();

                this.openAlfred();

                return;
            }

            if (event.key === 'Shift') {
                // Double shift pressed now?
                if (this.alfred.doubleShift) {
                    event.preventDefault();

                    this.alfred.doubleShift = false;
                    this.openAlfred();
                }

                // First shift is pressed
                this.alfred.doubleShift = true;
                setTimeout(() => {
                    // Not fast enough..
                    this.alfred.doubleShift = false;
                }, 300);
            }
        },

        /**
         * Trigger Alfred's mouse event.
         *
         * @param {MouseEvent} event
         */
        triggerAlfredMouseEvent(event) {
            // Ignore when Alfred has close prevention
            if (this.alfred.closePrevention) {
                return;
            }

            // Close Alfred when clicking outside Alfred's element
            if (!this.$el.contains(event.target)) {
                event.stopPropagation();

                this.resetAlfred();
            }
        },

        /**
         * Trigger an item's keyboard event.
         *
         * @param {KeyboardEvent} event
         */
        triggerItemKeyboardEvent(event) {
            if (event.key === 'Escape') {
                if (this.action.active && this.alfred.prefixed && this.alfred.prefix) {
                    this.alfred.phrase = this.alfred.prefix;
                }

                return this.previousState(false);
            }

            // Ignore the other keys when the phrase is extended
            if (this.action.active && this.action.extendedPhrase) {
                return;
            }

            if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
                event.preventDefault();

                return this.moveItemFocus(event.key === 'ArrowUp' ? 'up' : 'down');
            }

            if (event.key === 'Enter') {
                let item = this.getFocusedItem();

                if (item) {
                    this.triggerItem(item, event);
                } else if (this.action.active) {
                    this.triggerAction(event);
                }
            }
        },

        /**
         * Trigger a shortcut keyboard event.
         *
         * @param {KeyboardEvent} event
         */
        triggerShortcutKeyboardEvent(event) {
            // Ignore if we more states available
            if (this.items.saved.length) {
                return;
            }

            let item = this.getItemByShortcut(event);

            if (item) {
                // Alfred will prevent phrase override for the response
                this.alfred.phraseOverridePrevention = true;

                this.openAlfred();
                this.triggerItem(item, event);
            }
        },

        /**
         * Trigger Alfred's keyboard event.
         *
         * @param {KeyboardEvent} event
         */
        triggerSnippetKeyboardEvent(event) {
            // Do we have a timer active?
            if (this.snippets.timer) {
                clearTimeout(this.snippets.timer);
            }

            // Start timer to trigger a snippet
            this.snippets.timer = setTimeout(() => {
                this.triggerSnippet(event);
            }, this.snippets.timeout);
        },

        /**
         * Save current Alfred state.
         */
        saveState() {
            // Don't save the state when Alfred is initiating itself
            if (this.alfred.initiating) {
                this.alfred.initiating = false;

                return;
            }

            this.items.saved.push({
                action: {
                    active: this.action.active,
                    extendedPhrase: this.action.extendedPhrase,
                    realtime: this.action.realtime,
                    trigger: this.action.trigger,
                },
                alfred: {
                    phrase: this.alfred.phrase,
                    placeholder: this.alfred.placeholder,
                    prefixed: this.alfred.prefixed,
                    title: this.alfred.title,
                    triggered: this.alfred.triggered,
                },
                items: this.items.current,
            });

            // Reset action
            this.action.active = false;
            this.action.extendedPhrase = false;
            this.action.realtime = false;
            this.action.realtimeShouldDeactivate = false;

            // Reset prefixed state
            if (this.alfred.loaded && this.alfred.prefixed) {
                this.alfred.prefixed = false;
            }

            // Reset items
            this.items.current = [];
            this.items.filtered = [];
        },

        /**
         * Reset Alfred to previous state.
         *
         * @param {boolean} clearSavedStates
         */
        previousState(clearSavedStates) {
            // Close Alfred when zero states are available
            if (!this.items.saved.length) {
                return this.closeAlfred();
            }

            let state = this.items.saved.pop();

            // Should we clear saved states?
            if (clearSavedStates && this.items.saved.length) {
                state = this.items.saved.shift();

                this.items.saved = [];
            }

            this.updateAlfredState(state);
            this.filterItems();

            // Close Alfred when it was initiated globally, and we've reached the closing point
            if (this.alfred.initiatedGlobally && !this.items.saved.length) {
                this.closeAlfred();

                // Only reset initiated globally, when we haven't just reset everything for it
                if (!clearSavedStates) {
                    this.alfred.initiatedGlobally = false;
                }
            }
        },

        /**
         * Update Alfred's state.
         *
         * @param {Object|null} state
         */
        updateAlfredState(state) {
            // Do we have a timer active?
            if (this.action.timer) {
                clearTimeout(this.action.timer);
            }

            // Actual state available?
            if (!state) {
                return;
            }

            // Alfred state available?
            if (state.alfred || null) {
                // Only allow phrase changes when we are not in prefixed state
                if (!this.alfred.prefixed) {
                    // Update phrase when it's not prevented, or prevented but current phrase is empty
                    if (!this.alfred.phraseOverridePrevention || (this.alfred.phraseOverridePrevention && !this.alfred.phrase)) {
                        this.alfred.phrase = state.alfred.phrase;
                    }

                    // Do we have a phrase prevention? We've just handled it so reset if so.
                    if (this.alfred.phraseOverridePrevention) {
                        this.alfred.phraseOverridePrevention = false;
                    }
                }

                this.alfred.placeholder = state.alfred.placeholder;
                this.alfred.prefixed = state.alfred.prefixed;
                this.alfred.title = state.alfred.title;
                this.alfred.triggered = state.alfred.triggered;
            } else if (!this.alfred.prefixed) {
                this.alfred.phrase = '';
            }

            // Action state available?
            if (state.action || null) {
                this.setAction(state.action);
            }

            // Items state available?
            if (state.items || null) {
                this.setItems(state.items);
            }
        },

        /**
         * Set an action state.
         *
         * @param {Object} action
         */
        setAction(action) {
            this.action.active = action.active;
            this.action.extendedPhrase = action.extendedPhrase;
            this.action.realtime = action.realtime;
            this.action.trigger = action.trigger;
        },

        /**
         * Set an item set state.
         *
         * @param {Object} items
         */
        setItems(items) {
            this.items.current = items || [];
            this.items.filtered = [];

            this.filterItems();
        },

        /**
         * Display a message.
         *
         * @param {string} type
         * @param {string} text
         */
        displayMessage(type, text) {
            this.messages.current.push({
                type: type,
                text: text
            });

            setTimeout(() => {
                this.messages.current.shift();
            }, this.messages.timeout);
        },

        /**
         * Display a notification.
         *
         * @param {string} icon
         * @param {string} text
         */
        displayNotification(icon, text) {
            this.$swal({
                text: text,
                icon: icon,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', this.$swal.stopTimer)
                    toast.addEventListener('mouseleave', this.$swal.resumeTimer)
                },
            });
        },

        /**
         * Get Alfred's phrase.
         *
         * @return {string}
         */
        getPhrase() {
            return this.alfred.prefixed && this.alfred.prefix
                   ? this.alfred.phrase.substring(this.alfred.prefix.length + 1)
                   : this.alfred.phrase;
        },

        /**
         * Get current Alfred data.
         *
         * @param {Object} workflowStep
         *
         * @return {Object}
         */
        getAlfredData(workflowStep) {
            let data = {
                phrase: this.getPhrase(),
                realtime: this.action.active && (this.action.realtime || this.alfred.prefixed),
                workflowStep: workflowStep,
            };

            if (workflowStep.includeLocalStorageKeys) {
                data.workflowStep.localStorage = {};

                for (let storageKey of workflowStep.includeLocalStorageKeys) {
                    data.workflowStep.localStorage[storageKey] = this.getLocalStorageData(storageKey);
                }
            }

            return data;
        },

        /**
         * Get current page data.
         *
         * @return {Object}
         */
        getPageData() {
            return {
                document: {
                    title: document.title,
                },
                url: {
                    path: window.location.pathname,
                    hash: window.location.hash,
                    query: window.location.search,
                },
                focusableFields: this.getPageFocusableFields(),
            };
        },

        /**
         * Get current page's focusable fields.
         *
         * @return {Array}
         */
        getPageFocusableFields() {
            const fields = document.getElementsByClassName('form-group');

            return Array.from(fields).map(formGroup => {
                for (let child of formGroup.children) {
                    // Only add fields from a form-group that have a label and a field
                    if (child.tagName === 'LABEL' && child.htmlFor && child.innerText) {
                        return {
                            id: child.htmlFor,
                            name: child.innerText
                        }
                    }
                }

                return null;
            }).filter(fieldFocus => {
                // Remove the fields that didn't have a label
                if (fieldFocus === null) {
                    return false;
                }

                // Check if field is visible
                const element = document.getElementById(fieldFocus.id);

                return element ? element.offsetParent !== null : false;
            })
        },

        /**
         * Get Alfred's local storage data.
         *
         * @param {string} key
         *
         * @return {Array|null}
         */
        getLocalStorageData(key) {
            const currentTimestamp = Math.round(Date.now() / 1000),
                  localStorageKey = key.startsWith('alfred-') ? key : 'alfred-' + key,
                  localStorageData = localStorage.getItem(localStorageKey);

            if (localStorageData) {
                let localStorageDecoded = JSON.parse(localStorageData);

                // Does the local storage data have an expiration and is it still valid?
                if ('expiration' in localStorageDecoded && localStorageDecoded.expiration < currentTimestamp) {
                    localStorage.removeItem(localStorageKey);
                } else {
                    return localStorageDecoded;
                }
            }

            return null;
        },

        /**
         * Add something to Alfred's local storage data.
         *
         * @param {string} key
         * @param {Array} data
         * @param {number} ttl
         */
        setLocalStorageData(key, data, ttl) {
            const currentTimestamp = Math.round(Date.now() / 1000),
                  localStorageKey = key.startsWith('alfred-') ? key : 'alfred-' + key;

            // Do we need to add an expiration date?
            if ((ttl || 0) !== 0) {
                data = data.concat({ expiration: currentTimestamp + ttl });
            }

            localStorage.setItem(localStorageKey, JSON.stringify(data));
        },

        /**
         * Add item usage to stats.
         *
         * @param {Object} item
         */
        addItemUsage(item) {
            // Only save usages on registered items
            if (this.items.saved.length) {
                return;
            }

            // Make sure we have the correct item
            if ('obj' in item) {
                item = item.obj;
            }

            // Make sure we have a core item
            if (item.type !== 'Item') {
                return;
            }

            // Get current item usages
            let itemUsages = this.getLocalStorageData('item-usages') ?? {};

            // Update or set the usage of this item
            itemUsages[item.name] = (itemUsages[item.name] || 0) + 1;

            this.setLocalStorageData('item-usages', itemUsages, 0);
        },

        /**
         * Get the item by its ID.
         *
         * @param {number} id
         *
         * @return {Object|null}
         */
        getItemById(id) {
            let item = this.items.filtered[id];

            return item || null;
        },

        /**
         * Get the item by its prefix.
         *
         * @return {Object|null}
         */
        getItemByPrefix() {
            // Do we have a prefix?
            if (!this.alfred.prefix) {
                return null;
            }

            // Do we have an item available with this prefix?
            let item = this.items.current.filter(item => {
                return item.prefix === this.alfred.prefix;
            });

            return item.length ? item[0] : null;
        },

        /**
         * Get the item by its shortcut.
         *
         * @param {KeyboardEvent} event
         *
         * @return {Object|null}
         */
        getItemByShortcut(event) {
            // Did we only trigger one of the following keys?
            if (['Alt', 'Control', 'Meta', 'Shift'].indexOf(event.key) >= 0) {
                return null;
            }

            // Did we even trigger enough keys?
            if (!event.altKey && !event.ctrlKey && !event.metaKey && !event.shiftKey) {
                return null;
            }

            // Get shortcut based on pressed keys
            let shortcut = [];
            if (event.altKey) {
                shortcut.push('alt');
            }
            if (event.ctrlKey || event.metaKey) {
                shortcut.push('ctrl');
            }
            if (event.shiftKey) {
                shortcut.push('shift');
            }
            if (event.code.includes('Key', 0)) {
                shortcut.push(event.code.replace('Key', '').toLowerCase());
            } else if (event.key) {
                shortcut.push(event.key.toLowerCase());
            }

            // Do we have an item available with this shortcut?
            let item = this.items.current.filter(item => {
                let matched = 0,
                    itemShortcut = JSON.parse(JSON.stringify(item.shortcut));

                // Check if the arrays are the same length
                if (!itemShortcut || shortcut.length !== itemShortcut.length) {
                    return false;
                }

                // Check if all keys exist
                for (let index = 0; index < itemShortcut.length; index++) {
                    if (shortcut.indexOf(itemShortcut[index].toLowerCase()) >= 0) {
                        matched++;
                    }
                }

                // We found one, when we've matched all keys
                return matched === itemShortcut.length;
            });

            if (!item.length) {
                return null;
            }

            // Make sure we don't trigger other browser stuff based on this combination!
            event.preventDefault();

            return item[0];
        },

        /**
         * Get the focused item.
         *
         * @return {Object|null}
         */
        getFocusedItem() {
            let item = this.items.filtered.filter(item => {
                return item.focus;
            });

            return item.length ? item[0] : null;
        },

        /**
         * Get the element of the focused item.
         *
         * @return element|null
         */
        getFocusedItemElement() {
            let itemElement = this.$el.getElementsByClassName('alfred__item--focus')[0];

            return itemElement || null;
        },

        /**
         * Focus the given item.
         *
         * @param {Object} item
         */
        setItemFocus(item) {
            // Focus the correct item
            this.items.filtered = this.items.filtered.map(filteredItem => {
                filteredItem.focus = filteredItem === item;

                return filteredItem;
            });
        },

        /**
         * Move the focus to a different item.
         *
         * @param {string} direction
         */
        moveItemFocus(direction) {
            let focusedItem = this.getFocusedItem();

            if (focusedItem) {
                let id = focusedItem.id,
                    newFocusedItem = this.getItemById(direction === 'down' ? id + 1 : id - 1);

                if (newFocusedItem) {
                    this.setItemFocus(newFocusedItem);

                    // Keep it visible in Alfred
                    this.getFocusedItemElement().scrollIntoView(direction === 'down');
                }
            }
        },

        /**
         * Filter items by phrase and display items.
         */
        filterItems() {
            // Do we have any items to filter?
            if (!this.items.current.length) {
                return;
            }

            // Fuzzy filter items
            // The -1000 makes sure Fuzzysort we don't add bad results but narrowly filtered on the phrase.
            // The -100 makes sure Fuzzysort gives the name of the item a higher score than the description / info.
            let filtered = fuzzysort.go(this.getPhrase(), this.items.current, {
                keys: ['name', 'info'],
                limit: 100,
                scoreFn: (a) => {
                    return Math.max(a[0] ? a[0].score : -1000, a[1] ? a[1].score - 100 : -1000)
                }
            });

            // Filter out the "empty" or items that should be shown in normal filter results
            filtered = filtered.filter(item => {
                if ((item[0] === null && item[1] === null) || ('obj' in item && item.obj.type === 'FallbackItem')) {
                    return;
                }

                return item;
            });

            // No filtered items, the registered set of items and a phrase? Only then display fallback items.
            if (!this.items.saved.length && !filtered.length && this.alfred.phrase) {
                let fallbackItems = this.items.current.filter(item => {
                    return item.type === 'FallbackItem';
                });

                this.renderItems(fallbackItems, true, []);

                return;
            }

            // No filtered items, empty phrase and the registered set of items? Display popular item usages.
            if (!this.items.saved.length && !filtered.length && !this.getPhrase()) {
                // Get current item usages
                const itemUsages = this.getLocalStorageData('item-usages') ?? {};

                let popularItems = this.items.current.filter(item => {
                    return item.name in itemUsages;
                }).sort((a, b) => {
                    return itemUsages[b.name] - itemUsages[a.name];
                }).filter((item, index) => {
                    return index < 5; // Maximum amount of popular items
                });

                this.renderItems(popularItems, false, itemUsages);

                return;
            }

            // No filtered items, empty phrase and not the registered set of items? Only then display all available items.
            if (this.items.saved.length && !filtered.length && !this.getPhrase()) {
                this.renderItems(this.items.current, false, []);

                return;
            }

            // Render fuzzy filtered items
            this.renderItems(filtered, false, []);
        },

        /**
         * Render the items to be displayed.
         *
         * @param {Object[]} filteredItems
         * @param {boolean} fallbacked
         * @param {string[]} itemUsages
         */
        renderItems(filteredItems, fallbacked, itemUsages) {
            let counter = -1;

            // Reset filtered items
            this.items.filtered = [];

            for (let filteredItem of filteredItems) {
                let item = fallbacked ? JSON.parse(JSON.stringify(filteredItem)) : filteredItem,
                    name = item.name,
                    info = item.info;

                if (fallbacked) {
                    // Overwrite in fallback state
                    name = item.name + " '" + this.alfred.phrase + "'";
                    info = '';
                } else if ('obj' in filteredItem) {
                    // Overwrite values
                    name = filteredItem.obj.name;
                    info = filteredItem.obj.info;
                    item = {...item, ...filteredItem.obj};

                    // Highlight our best item
                    if (filteredItem[0] !== null) {
                        if (filteredItem[0].target === info) {
                            info = fuzzysort.highlight(filteredItem[0]);
                        } else {
                            name = fuzzysort.highlight(filteredItem[0]);
                        }
                    } else if (filteredItem[1] !== null) {
                        if (filteredItem[1].target === info) {
                            info = fuzzysort.highlight(filteredItem[1]);
                        } else {
                            name = fuzzysort.highlight(filteredItem[1]);
                        }
                    } else {
                        continue;
                    }
                }

                // Update current item values
                counter++;
                item.id = counter;
                item.name = name;
                item.info = info;
                item.focus = counter === 0;
                item.usage = itemUsages[item.name] || 0;

                // Add the item to our current filtered items
                this.items.filtered.push(item);
            }
        },

        /**
         * Trigger the action.
         *
         * @param {KeyboardEvent|null} event
         */
        triggerAction(event) {
            // Do we have a timer active?
            if (this.action.timer) {
                clearTimeout(this.action.timer);
            }

            // Reset items
            this.items.current = [];
            this.items.filtered = [];

            // Remember the action that was triggered
            this.alfred.triggered = this.action;

            // Trigger the action's preparation
            this.handlePreparedTrigger(this.action.trigger, event, null);
        },

        /**
         * Trigger the clipboard.
         *
         * @param {Object} clipboard
         * @param {MouseEvent|KeyboardEvent|null} event
         */
        triggerClipboard(clipboard, event) {
            // Make sure we don't trigger other event based stuff
            if (event) {
                event.preventDefault();
            }

            // Do we need to display a notification?
            if (clipboard.notification) {
                this.displayNotification('success', clipboard.notification);
            }

            // Support clipboard API?
            if (navigator.clipboard) {
                navigator.clipboard.writeText(clipboard.text);

                // Clipboard is updated; Close Alfred!
                this.resetAlfred();

                return;
            }

            // Prepare to copy text
            this.action.active = true;
            this.action.extendedPhrase = true;
            this.alfred.phrase = clipboard.text;

            // Copy the text!
            this.$nextTick(() => {
                this.$refs.phraseInput.focus();
                this.$refs.phraseInput.select();

                document.execCommand('copy');

                // Reset preparation
                this.action.active = false;
                this.alfred.extendedPhrase = false;

                // Clipboard is updated; Close Alfred!
                this.resetAlfred();
            });
        },

        /**
         * Trigger a Vue component.
         *
         * @param {Object} component
         * @param {MouseEvent|KeyboardEvent|null} event
         */
        triggerComponent(component, event) {
            let vueComponent = this.$root.$refs[component.reference] || null;

            if (!vueComponent) {
                return;
            }

            // Make sure we don't trigger other event based stuff
            if (event) {
                event.preventDefault();
            }

            // @todo This should definitely be refactored at a later time to allow recursive data adjustments
            // Which keys do we have available?
            if (component.data.settings.visible || null) {
                vueComponent.settings.visible = component.data.settings.visible;
            }

            // Component is triggered; Close Alfred!
            this.resetAlfred();
        },

        /**
         * Focus a field.
         *
         * @param {Object} field
         * @param {MouseEvent|KeyboardEvent|null} event
         */
        triggerFieldFocus(field, event) {
            let element = document.getElementById(field.id);

            // Do we have the element?
            if (!element) {
                return;
            }

            // Make sure we don't trigger other event based stuff
            if (event) {
                event.preventDefault();
            }

            element.focus();

            // Field is focused; Close Alfred!
            this.resetAlfred();
        },

        /**
         * Fill a value in a field.
         *
         * @param {Object} field
         * @param {MouseEvent|KeyboardEvent|null} event
         */
        triggerFillFieldValue(field, event) {
            let element = document.getElementById(field.id);

            // Do we have the element?
            if (!element) {
                return;
            }

            // Make sure we don't trigger other event based stuff
            if (event) {
                event.preventDefault();
            }

            // Fill the given value for the element
            element.value = field.value;

            // Do we need to display a notification?
            if (field.notification) {
                this.displayNotification('success', field.notification);
            }

            // Value is filled in field; Close Alfred!
            this.resetAlfred();
        },

        /**
         * Trigger an item.
         *
         * @param {Object} item
         * @param {MouseEvent|KeyboardEvent} event
         */
        triggerItem(item, event) {
            // Make sure this item is now focused
            let focusedItem = this.getFocusedItem();
            if (!focusedItem || focusedItem.id !== item.id) {
                this.setItemFocus(item);
            }

            // Do we have to warn the user about this trigger?
            if (item.warn) {
                // Don't trigger swal's Enter KeyboardEvent
                event.preventDefault();

                this.$swal({
                    title: 'Are you sure you want to do this?',
                    icon: 'warning',
                    showCloseButton: true,
                    showCancelButton: true,
                    confirmButtonColor: '#CC3E29',
                    onOpen: () => {
                        this.alfred.closePrevention = true;
                    },
                    onAfterClose: () => {
                        this.alfred.closePrevention = false;
                    }
                }).then((result) => {
                    if (result.value || null) {
                        this.handleItemTrigger(item, event, true);
                    }
                });

                return;
            }

            this.handleItemTrigger(item, event, true);
        },

        /**
         * Trigger a prefixed item.
         *
         * @param {Object} item
         */
        triggerPrefixedItem(item) {
            // Do we support this prefixed item's trigger?
            if (['Action', 'ItemSet'].indexOf(item.trigger.type) === -1) {
                return;
            }

            this.alfred.prefixed = true;

            this.handlePreparedTrigger(item.trigger, null, item.trigger.properties);

            this.alfred.prefixed = true;

            // Immediately trigger the action if a phrase is already present
            if (item.trigger.type === 'Action' && this.getPhrase().length) {
                this.triggerAction(null);
            } else if (item.trigger.type === 'ItemSet') {
                this.filterItems();
            }
        },

        /**
         * Trigger the local storage to store new data.
         *
         * @param {Object} localStorage
         * @param {MouseEvent|KeyboardEvent|null} event
         */
        triggerLocalStorage(localStorage, event) {
            // Make sure we don't trigger other event based stuff
            if (event) {
                event.preventDefault();
            }

            // Get current local storage data for key
            let data = this.getLocalStorageData(localStorage.key) ?? {};

            // Do we need to merge the data?
            data = localStorage.merge ? {...data, ...localStorage.data} : localStorage.data;

            // Save to local storage!
            this.setLocalStorageData(localStorage.key, data, localStorage.number);

            // Do we need to display a notification?
            if (localStorage.notification) {
                this.displayNotification('success', localStorage.notification);
            }

            // Local storage updated; Close Alfred!
            this.resetAlfred();
        },

        /**
         * Trigger a redirect.
         *
         * @param {Object} redirect
         * @param {MouseEvent|KeyboardEvent|null} event
         */
        triggerRedirect(redirect, event) {
            // Make sure we don't trigger other event based stuff
            if (event) {
                event.preventDefault();
            }

            // Ajax URL
            if (redirect.type === 'ajax') {
                this.alfred.loading = true;

                this.$http.post(redirect.url, {}).then(
                    response => {
                        // No longer loading
                        this.alfred.loading = false;
                        this.closeAlfred();

                        window.location.reload();
                    },
                    response => {
                        // No longer loading
                        this.alfred.loading = false;
                        this.closeAlfred();

                        window.location.reload();
                    }
                );

                return;
            }

            // Regular URL
            if (redirect.window === 'new' || (event && (event.metaKey || event.ctrlKey)) || (event && event.button === 1)) {
                window.open(redirect.url);

                return;
            }

            window.location = redirect.url;

            this.closeAlfred();
        },

        /**
         * Trigger a state reload.
         *
         * @param {Object} reloadState
         * @param {MouseEvent|KeyboardEvent} event
         */
        triggerReloadState(reloadState, event) {
            // Go back x amount of steps
            for (let step = 0; step < reloadState.steps; step++) {
                this.previousState(false);
            }

            // Re-trigger the remembered action / item
            if (this.alfred.triggered) {
                if (this.action.active) {
                    this.triggerAction(event);

                    return;
                }

                this.handleItemTrigger(this.alfred.triggered, event, false);
            }
        },

        /**
         * Trigger a snippet.
         *
         * @param {KeyboardEvent} event
         */
        triggerSnippet(event) {
            // Do we have a timer active?
            if (this.snippets.timer) {
                clearTimeout(this.snippets.timer);
            }

            let target = null;

            // Only trigger inside Alfred or HTML inputs
            if (this.alfred.visible) {
                target = this.$refs.phraseInput;
            } else if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
                target = event.target;
            }

            if (!target || !target.value) {
                return;
            }

            let snippets = this.getLocalStorageData('snippets') ?? {};

            for (const keyword in snippets) {
                if (target.value.includes(keyword)) {
                    target.value = target.value.replaceAll(keyword, snippets[keyword]);
                }
            }
        },

        /**
         * Trigger a workflow step.
         *
         * @param {Object} workflowStep
         * @param {MouseEvent|KeyboardEvent|null} event
         */
        triggerWorkflowStep(workflowStep, event) {
            // Make sure we don't trigger other event based stuff
            if (event) {
                event.preventDefault();
            }

            // Fire the request
            this.handleWorkflowStepRequest(workflowStep);
        },

        /**
         * Handle global (views / JS) workflow step triggers.
         *
         * @param {Object} workflowStep
         */
        handleGlobalWorkflowStepTrigger(workflowStep) {
            // Is Alfred initiated?
            if (!this.alfred.initiated) {
                return;
            }

            // Make sure Alfred is reset
            this.resetAlfred();

            // Alfred is initiated globally!
            this.alfred.initiatedGlobally = true;

            // Make sure Alfred is open and STAYS open!
            this.alfred.closePrevention = true;
            this.openAlfred();

            // Initiate Alfred with given trigger
            this.triggerWorkflowStep(workflowStep, null);
        },

        /**
         * Handle item trigger.
         *
         * @param {Object} item
         * @param {MouseEvent|KeyboardEvent} event
         * @param {boolean} storeItemUsage
         */
        handleItemTrigger(item, event, storeItemUsage) {
            if (storeItemUsage) {
                this.addItemUsage(item);
            }

            // Did we get triggered through a realtime action?
            if (this.action.active && (this.action.realtime || this.alfred.prefixed)) {
                this.action.realtimeShouldDeactivate = true;
            }

            // Remember the item that was triggered
            this.alfred.triggered = item;

            // Trigger the item's preparation
            this.handlePreparedTrigger(item.trigger, event, item.trigger.properties);
        },

        /**
         * Handle prepared trigger.
         *
         * @param {Object} trigger
         * @param {MouseEvent|KeyboardEvent|null} event
         * @param {Object|null} alfredState
         */
        handlePreparedTrigger(trigger, event, alfredState) {
            // Do we want to trigger an action?
            if (trigger.type === 'Action') {
                this.saveState();
                this.updateAlfredState(alfredState);

                // Stop the event for items that were triggered by mouse and should initiate an action
                if (event) {
                    event.stopPropagation();
                    event.preventDefault();
                }

                return this.setAction(trigger.properties);
            }

            // Do we want to write to the clipboard?
            if (trigger.type === 'Clipboard') {
                return this.triggerClipboard(trigger.properties, event);
            }

            // Do we want to trigger a component?
            if (trigger.type === 'Component') {
                return this.triggerComponent(trigger.properties, event);
            }

            // Do we want to trigger a field focus?
            if (trigger.type === 'FieldFocus') {
                return this.triggerFieldFocus(trigger.properties, event);
            }

            // Do we want to trigger a fill field value?
            if (trigger.type === 'FillFieldValue') {
                return this.triggerFillFieldValue(trigger.properties, event);
            }

            // Do we want to trigger an item set?
            if (trigger.type === 'ItemSet') {
                // Stop the event for items that were triggered by mouse
                if (event) {
                    event.stopPropagation();
                    event.preventDefault();
                }

                // Just update through action?
                if (this.action.active
                    && (this.action.realtime || this.alfred.prefixed)
                    && !this.action.realtimeShouldDeactivate
                ) {
                    return this.setItems(trigger.properties.items);
                }

                this.saveState();
                this.updateAlfredState(alfredState);

                return this.setItems(trigger.properties.items);
            }

            // Do we want to store local storage?
            if (trigger.type === 'LocalStorage') {
                return this.triggerLocalStorage(trigger.properties, event);
            }

            // Do we want to trigger a redirect?
            if (trigger.type === 'Redirect') {
                return this.triggerRedirect(trigger.properties, event);
            }

            // Do we want to trigger a state reload?
            if (trigger.type === 'ReloadState') {
                return this.triggerReloadState(trigger.properties, event);
            }

            // Do we want to trigger a workflow step?
            if (trigger.type === 'WorkflowStep') {
                return this.triggerWorkflowStep(trigger.properties, event);
            }
        },

        /**
         * Handle workflow step request.
         *
         * @param {Object} workflowStep
         */
        handleWorkflowStepRequest(workflowStep) {
            // Only one request at a time!
            if (this.alfred.loading) {
                return;
            }

            this.alfred.loading = true;
            this.$http.post('/alfred/handle-workflow-step', {
                alfred: this.getAlfredData(workflowStep),
                page: this.getPageData(),
            }).then(
                response => {
                    // No longer loading
                    this.alfred.loading = false;

                    // Was Alfred closed in the meantime?
                    if (!this.alfred.visible) {
                        return this.resetAlfred();
                    }

                    // Did our request succeed?
                    if (response.status !== 200) {
                        return this.displayMessage('error', 'Something went wrong.');
                    }

                    this.handleWorkflowStepResponse(response.body);
                },
                response => {
                    // No longer loading
                    this.alfred.loading = false;

                    this.displayMessage('error', 'Something went wrong.');
                }
            );
        },

        /**
         * Handle the workflow step response.
         *
         * @param {Object} response
         */
        handleWorkflowStepResponse(response) {
            // When close prevention was given; remove it!
            if (this.alfred.closePrevention) {
                this.alfred.closePrevention = false;
            }

            // Display a message?
            if (response.message) {
                this.displayMessage(response.success ? 'success' : 'error', response.message);
            }

            // Display a notification?
            if (response.notification) {
                this.displayNotification(response.success ? 'success' : 'error', response.notification);
            }

            // Did our trigger succeed?
            if (!response.success) {
                return;
            }

            // Did we receive something to trigger?
            if (response.trigger) {
                this.alfred.loaded = true;

                this.handlePreparedTrigger(response.trigger, null, response);

                this.alfred.loaded = false;

                return;
            }

            // Everything was successful; reset to the previous state.
            this.previousState(false);
        }
    }
}
