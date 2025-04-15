import { library } from '@fortawesome/fontawesome-svg-core'; // Import the fontawesome core
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'; // Import the FontAwesomeIcon component
import { fas } from '@fortawesome/free-solid-svg-icons'; // Import the entire solid icon set

library.add(fas); // Add the icons to the library so we can use it in our app

import Alfred from "./components/Alfred.vue";

const AlfredApp = {
    install(Vue) {
        Vue.component('font-awesome-icon', FontAwesomeIcon);

        // Let's register our component globally
        // https://vuejs.org/v2/guide/components-registration.html
        Vue.component("alfred", Alfred);

        const root = new Vue();

        // Make Alfred available in all components
        Vue.prototype.$alfred = {
            listener: root,

            /**
             * Trigger a workflow step.
             *
             * @param {string} workflowStepClass
             * @param {?Object} workflowStepData
             * @param {?string} workflowStepMethod
             */
            triggerWorkflowStep: (workflowStepClass, workflowStepData, workflowStepMethod) => {
                root.$emit(`alfredTriggerWorkflowStep`, {
                    class: workflowStepClass,
                    data: workflowStepData || {},
                    method: workflowStepMethod || 'handle',
                });
            },

            /**
             * Trigger a workflow step with a warning.
             *
             * @param {string} workflowStepClass
             * @param {?Object} workflowStepData
             * @param {?string} workflowStepMethod
             */
            triggerWarnedWorkflowStep: (workflowStepClass, workflowStepData, workflowStepMethod) => {
                root.$emit(`alfredTriggerWarnedWorkflowStep`, {
                    class: workflowStepClass,
                    data: workflowStepData || {},
                    method: workflowStepMethod || 'handle',
                });
            },
        };
    }
};

// Automatic installation if Vue has been added to the global scope.
if (typeof window !== 'undefined' && window.Vue) {
    window.Vue.use(AlfredApp);
}

export default AlfredApp;
