<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 3/15/2017
 * Time: 3:28 PM
 */

namespace AppBundle\AppException;


class AppExceptionMessages
{
    const GENERAL_ERROR_MESSAGE = "An error occurred, Please try again. If this problem persists, Please contact Support.";
    const DIRECTORY_NOT_FOUND = "Destination directory not found";
    const CANNOT_REACH_PROCESSING_ENDPOINT = "Could not reach FCC Middleware";

    const DUPLICATE_OPEN_PERMISSION = "An open permission request for this year already exists";
    const DUPLICATE_TITLE = "Title already exists";
    const DUPLICATE_DESCRIPTION = "Description already exists";
    const DUPLICATE_FILE_NAME = "File name already exists";

    const OPERATION_NOT_ALLOWED = "You cannot perform this operation at this time. For clarifications, please contact support";
    const DUPLICATE_SUBMISSION_ID = "Submission ID Already in Use. Please Try Again.";
    const DUPLICATE_ACTIVE_SUBMISSION = "An active main submission for this year already exists. Please delete it and try Again.";

    const PREV_FAILED_SUBMISSION = "An existing %d submission, Previously failed validation. <br/><br/>Please delete and correct this invalid submission and try your submission again.";
    const BELOW_BASELINE_YEAR = "You cannot make a submission for a year bellow your Organizations baseline year.";
    const NOT_PERMITTED_YEAR = "You need permission to make %d submission at this time.
                                <br/><br/>Looks like you skipped submission for %d.
                                <br/><br/>To continue submission for %d, Please request permission and try again when it is  approved.";
    const DUPLICATE_MAIN_SUBMISSION = "A submission already exists for this year. 
                                        <br/><br/>Please request permission for this submission and try again when it is approved.";
    const DUPLICATE_MAIN_SUBMISSION_2 = "A submission already exists for this year. 
                                        <br/><br/>Please request a permission for this submission and try again when it is approved.";
    const SKIPPED_PREVIOUS_YEAR = "You need permission to make submission for %d.
                        <br/><br/>This is because, No submission was found for previous year %d.
                        <br/><br/>To continue submission for %d, Please request permission and try again when it is  approved.";

    const INVALID_QUARTERLY_RETURN_YEAR = "You can only submit QUARTERLY RETURN for the year of your most recent MAIN SUBMISSION.";
    const NO_PROCESSED_MDA_REPORT = "You do not have any active processed submission.";
    const MDA_NAME_MISMATCH = "MDA Names do not match";
    const USER_ALREADY_ACTIVATED = "User is already activated";
}