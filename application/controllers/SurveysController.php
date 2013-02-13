<?php 

    class SurveysController extends LSYii_Controller
    {
        
        public function actionIndex()
        {
            $overview = array();
            $surveys = Survey::model()->with('languagesettings','owner')->findAll();
            foreach ($surveys as $survey)
            {
                // Get localized title.
                if (in_array(App()->getConfig('adminlang'), $survey->getLanguages()))
                {
                    $language = App()->getConfig('adminlang');
                }
                else 
                {
                    $language = $survey->language;;
                }
                
                foreach ($survey->languagesettings as $languagesetting)
                {
                    if ($language == $languagesetting->surveyls_language)
                    {
                        $title = $languagesetting->surveyls_title;
                    }
                }
                
                // Get total number of responses and completes.
                if ($survey->active == 'Y')
                {
                    $total = Survey_dynamic::model($survey->sid)->count();
                    $condition = new CDbCriteria();
                    $condition->addNotInCondition('submitdate', null);
                    $completed = Survey_dynamic::model($survey->sid)->count($condition);
                }
                $row = array(
                    'sid' => $survey->sid,
                    'title' => $title,
                    'active' => $survey->active == 'Y',
                    'owner' => array(
                        'name' => $survey->owner->full_name,
                        'id' => $survey->owner->uid,
                    ),
                    'created' => $survey->datecreated,
                    'open' => $survey->usetokens == 'N',
                    'anonymized' => $survey->anonymized == 'Y',
                    'completed' => $completed,
                    'partial' => $total - $completed,
                    'total' => $total,
                );
                
                $overview[] = $row;
                
            }
            
                    
            $this->render('/surveys/index', compact('overview'));
        }
        
        
        /**
         * Survey overview. 
         * @param int $sid
         */
        public function actionView($sid)
        {
            $survey = Survey::model()->findByPk($sid);
            if ($survey != null)
            {
                $this->navData['surveyId'] = $sid;
                $this->render('/surveys/view', compact('survey'));
            }
            else
            {
                App()->user->setFlash('surveys', gt('Could not find survey.'));
                $this->redirect(array('surveys/index'));
            }
            
        }
    }
?>