import Alfred from "./components/Alfred.vue";

const AlfredApp = {
    install(Vue) {
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
