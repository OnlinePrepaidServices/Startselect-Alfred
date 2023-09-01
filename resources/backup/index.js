import Alfred from './Alfred';
import mitt from 'mitt';

window.mitt = window.mitt || new mitt();

export default {
    install (Vue, options) {

        const root = new Vue();

        // Create Alfred component
        Vue.component('alfred', Alfred);

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

        // Make Alfred available in other Vue instances
        window.mitt.on('alfredTriggerWorkflowStep', (workflowStep) => {
            root.$emit(`alfredTriggerWorkflowStep`, {
                class: workflowStep.class,
                data: workflowStep.data,
                method: workflowStep.method
            });
        });
        window.mitt.on('alfredTriggerWarnedWorkflowStep', (workflowStep) => {
            root.$emit(`alfredTriggerWarnedWorkflowStep`, {
                class: workflowStep.class,
                data: workflowStep.data,
                method: workflowStep.method
            });
        });
    }
}
