<?php

class className extends JobRouter\Engine\Runtime\PhpFunction\BoxActionFunction
{
    public function execute()
    {
        $selectedWorkflowIds = $this->getWorkflowIds();

        foreach ($selectedWorkflowIds as $workflowId) {

            $step = $this->getStepByWorkflowId($workflowId);
            $processid = $step->getProcessId();
            $stepid    = $step->getStepId();
            //$stepid    = $this->getStepIds();
            // $jobDB = $this->getJobDB(); 
            // to be deleted, redundant

        }
        return true;
    }
}
?>