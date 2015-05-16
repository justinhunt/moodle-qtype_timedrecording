The timed recording question is a modified version of the PoodLL Audio Recording question. A requirement of the timed recording question is that the PoodLL filter also be installed. A Red5 Server with the PoodLL web application will also need to be available. It is possible to use the PoodLL Cloud server at tokyo.poodll.com.

To install the timed recording question type, unzip the timedquestion.zip archive. It will contain one filed the timed folder. Place this in the [Moodle Program Directory]/question/type folder. Then log into your Moodle site as an administrator and visit the notifications page. Moodle will present a short series of pages to guid you through the installation.

To create a timed question, from the question bank, choose add new and then choose the timed recording question option. This will present a familiar question settings page, with three extra fields.
1) Preparation time. The length of time the student has to prepare before recording starts automatically.
2) Recording time. The length of time the student has to record.
3) Auto Forward. If checked, upon the end of the recording time being reached, the "next" button will be automatically "pressed" and the next page in the quiz will be automatically fowarded to.

The timed question is designed to work in the context of the "sequential" mode that was introduced to quizzes in Moodle 2.3, and with only one question per page. Technically it would also work in normal mode too though it would not be very useful, since a reload of the page using the quizzes navigation buttons would restart the question.

On each question the student will see in order from the top of the page to the bottom of the page
1) the question description
2) the preparation and recording times
3) the countdown timer
4) the audio recorder
5) the "next" button.

On page load the preparation time countdown will begin. When it reaches 0 the recording time will begin counting down and the recorder will start recording automatically. Should the student start recording by pressing the "record" button, before the preparation time countdown has finished, the specified recording time countdown will start counting down at once. The student is also free to stop and restart recording within the recording time period, but the recording time will continue counting down relentlessly. Note the recording cannot be "paused," if the student restarts, their initial recording will simply be overwritten.

Grading for the recording question is done manually and an audio player will be displayed in the grading page for the grader to listen to the student's submission.

 Justin Hunt
 bitwalkerjapan@gmail.com