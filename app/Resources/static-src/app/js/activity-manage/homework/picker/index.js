import QuestionPicker from 'app/common/component/question-picker';
import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';

let $questionPickerBody = $('#question-picker-body',window.parent.document);
new QuestionPicker($questionPickerBody , $('#step2-form'));

new SelectLinkage($('[name="courseId"]',window.parent.document),$('[name="lessonId"]',window.parent.document));
