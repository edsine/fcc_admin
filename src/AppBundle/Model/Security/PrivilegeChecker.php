<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 4/17/2017
 * Time: 8:05 PM
 */

namespace AppBundle\Model\Security;


use AppBundle\Utils\AppConstants;

class PrivilegeChecker
{

    private $primaryRole;
    private $privileges;
    private $roles;
    private $username;
    private $establishmentCode;

    //RESERVED SYSTEM ROLES
    private $superAdmin = false;
    private $misHead = false;
    private $appUser = false;
    private $mdaAdmin = false;

    //ADMIN
    private $canManageCMS = false;
    private $canListCommittee = false;
    private $canModifyCommittee = false;
    private $canListFccUser = false;
    private $canModifyFccUser = false;
    private $canListMdaUser = false;
    private $canModifyMdaUser = false;
    private $canListFedMda = false;
    private $canModifyFedMda = false;
    private $canListStateMda = false;
    private $canModifyStateMda = false;
    private $canManageRoles = false;

    //FCC
    private $canListAssignedMdas = false;
    private $canListCommitteeMdas = false;
    private $canConfirmFedMdaNominalRollUpload = false;
    private $canConfirmStateMdaNominalRollUpload = false;

    //FED MDA
    private $canUploadFedMdaNominalRoll = false;
    private $canUploadFedMdaVacancy = false;

    //GENERAL REPORT
    private $canViewReports, $canViewFederalReports, $canViewStateReports;

    //FED REPORTS
    private $canRequestFedMdaCareerCharacterBalancingIndex = false;
    private $canViewFedMdaCareerCharacterBalancingIndex = false;
    private $canViewFedMdaCareerPostDist = false;
    private $canQueryFedMdaNominalRoll = false;

    //OTHER REPORTS
    private $canViewFedConsolidatedCareerPostDist = false;
    private $canViewFedComparativeDataOnStaffDist = false;
    private $canViewFedListOfChiefExecutives = false;
    private $canViewFedPOLOHByPostAndYearDist = false;
    private $canViewFedPOLOPostDist = false;

    //REPORT MDA SELECTION LIMIT
    private $canSelectAllMdas = false;
    private $canSelectOnlyAssignedMdas = false;
    private $canSelectOnlyCommitteeMdas = false;
    private $canSelectOnlyUserMda = false;

    //STATE MDA
    private $canUploadStateMdaNominalRoll = false;
    private $canUploadStateMdaVacancy = false;

    /**
     * PrivilegeChecker constructor.
     */
    public function __construct($userPrimaryRole, $userRoles, $userPrivileges, $username, $establishmentCode)
    {
        $this->primaryRole = $userPrimaryRole;
        $this->roles = $userRoles;
        $this->privileges = $userPrivileges;
        $this->username = $username;
        $this->establishmentCode = $establishmentCode;
    }

    public function initRoles()
    {

        switch ($this->primaryRole) {
            case AppConstants::ROLE_SUPER_ADMIN:
                $this->superAdmin = true;
                break;

            case AppConstants::ROLE_MIS_HEAD:
                $this->misHead = true;
                break;

            case AppConstants::ROLE_MDA_ADMIN:
                $this->mdaAdmin = ($this->username == $this->establishmentCode);
                break;
        }

        if (in_array(AppConstants::ROLE_USER, $this->roles)) {
            $this->appUser = true;
        }

        $this->initPrivileges();
    }

    private function initPrivileges()
    {
        $this->canManageCMS = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_CMS_MANAGE, $this->privileges);

        $this->canListCommittee = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_COMMITTEE_LIST, $this->privileges)
            || in_array(AppConstants::PRIV_COMMITTEE_MODIFY, $this->privileges);

        $this->canModifyCommittee = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_COMMITTEE_MODIFY, $this->privileges);

        $this->canListFccUser = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_FCC_USER_LIST, $this->privileges)
            || in_array(AppConstants::PRIV_FCC_USER_MODIFY, $this->privileges);

        $this->canModifyFccUser = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_FCC_USER_LIST, $this->privileges)
            || in_array(AppConstants::PRIV_FCC_USER_MODIFY, $this->privileges);

        $this->canListMdaUser = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_MDA_USER_LIST, $this->privileges)
            || in_array(AppConstants::PRIV_MDA_USER_MODIFY, $this->privileges);

        $this->canModifyMdaUser = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_MDA_USER_MODIFY, $this->privileges);

        $this->canListFedMda = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_FED_MDA_LIST, $this->privileges)
            || in_array(AppConstants::PRIV_FED_MDA_MODIFY, $this->privileges);

        $this->canModifyFedMda = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_FED_MDA_MODIFY, $this->privileges);

        $this->canListStateMda = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_STATE_MDA_LIST, $this->privileges)
            || in_array(AppConstants::PRIV_STATE_MDA_MODIFY, $this->privileges);

        $this->canModifyStateMda = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_STATE_MDA_MODIFY, $this->privileges);

        $this->canManageRoles = $this->superAdmin
            || $this->misHead
            || in_array(AppConstants::PRIV_ROLE_MANAGE, $this->privileges);

        $this->canListAssignedMdas = in_array(AppConstants::PRIV_ASSIGNED_MDA_LIST, $this->privileges);

        $this->canListCommitteeMdas = in_array(AppConstants::PRIV_COMMITTEE_MDA_LIST, $this->privileges);

        $this->canConfirmFedMdaNominalRollUpload = in_array(AppConstants::PRIV_CONFIRM_FED_MDA_NOMINAL_ROLL_UPLOAD, $this->privileges);

        $this->canConfirmStateMdaNominalRollUpload = in_array(AppConstants::PRIV_CONFIRM_STATE_MDA_NOMINAL_ROLL_UPLOAD, $this->privileges);

        $this->canUploadFedMdaNominalRoll = in_array(AppConstants::PRIV_FED_MDA_UPLOAD_NOMINAL_ROLL, $this->privileges);
        $this->canUploadFedMdaVacancy = in_array(AppConstants::PRIV_FED_MDA_UPLOAD_VACANCY, $this->privileges);

        $this->canRequestFedMdaCareerCharacterBalancingIndex = $this->canUploadFedMdaNominalRoll;
        $this->canViewFedMdaCareerCharacterBalancingIndex = $this->superAdmin || $this->misHead;
        $this->canViewFedMdaCareerPostDist = $this->superAdmin || $this->misHead || in_array(AppConstants::PRIV_FED_MDA_CAREER_POST_DIST, $this->privileges);
        $this->canQueryFedMdaNominalRoll = $this->superAdmin || $this->misHead || in_array(AppConstants::PRIV_FED_MDA_NOMINAL_ROLL_QUERY, $this->privileges);

        $this->canViewFedConsolidatedCareerPostDist = $this->superAdmin || $this->misHead || in_array(AppConstants::PRIV_FED_CONSOLIDATED_CAREER_POST_DIST, $this->privileges);
        $this->canViewFedComparativeDataOnStaffDist = $this->superAdmin || $this->misHead || in_array(AppConstants::PRIV_FED_LEVEL_COMPARATIVE_DATA_ON_STAFF_DIST, $this->privileges);
        $this->canViewFedListOfChiefExecutives = $this->superAdmin || $this->misHead || in_array(AppConstants::PRIV_FED_LEVEL_LIST_OF_CHIEF_EXECUTIVES, $this->privileges);
        $this->canViewFedPOLOHByPostAndYearDist = $this->superAdmin || $this->misHead || in_array(AppConstants::PRIV_FED_POLOH_BY_POST_AND_YEAR_DIST, $this->privileges);
        $this->canViewFedPOLOPostDist = $this->superAdmin || $this->misHead || in_array(AppConstants::PRIV_FED_POLOH_POST_DIST, $this->privileges);

        $this->canViewFederalReports = $this->superAdmin || $this->misHead
            || $this->canViewFedMdaCareerCharacterBalancingIndex || $this->canViewFedMdaCareerPostDist
            || $this->canViewFedConsolidatedCareerPostDist || $this->canViewFedComparativeDataOnStaffDist
            || $this->canViewFedListOfChiefExecutives || $this->canViewFedPOLOHByPostAndYearDist || $this->canViewFedPOLOPostDist;

        $this->canViewReports = $this->superAdmin || $this->misHead || $this->canViewFederalReports || $this->canViewStateReports;

        $this->canSelectAllMdas = in_array(AppConstants::PRIV_ALL_MDA_REPORT_SELECTION_LIMIT, $this->privileges);
        $this->canSelectOnlyAssignedMdas = in_array(AppConstants::PRIV_ASSIGNED_MDA_REPORT_SELECTION_LIMIT, $this->privileges);
        $this->canSelectOnlyCommitteeMdas = in_array(AppConstants::PRIV_COMMITTEE_MDA_REPORT_SELECTION_LIMIT, $this->privileges);
        $this->canSelectOnlyUserMda = in_array(AppConstants::PRIV_USER_MDA_REPORT_SELECTION_LIMIT, $this->privileges);


        $this->canUploadStateMdaNominalRoll = in_array(AppConstants::PRIV_STATE_MDA_UPLOAD_NOMINAL_ROLL, $this->privileges);
        $this->canUploadStateMdaVacancy = in_array(AppConstants::PRIV_STATE_MDA_UPLOAD_VACANCY, $this->privileges);
    }

    /**
     * @return boolean
     */
    public function isSuperAdmin(): bool
    {
        return $this->superAdmin;
    }

    /**
     * @return boolean
     */
    public function isMisHead(): bool
    {
        return $this->misHead;
    }

    /**
     * @return boolean
     */
    public function isAppUser(): bool
    {
        return $this->appUser;
    }

    /**
     * @return boolean
     */
    public function isCanManageCMS(): bool
    {
        return $this->canManageCMS;
    }

    /**
     * @return boolean
     */
    public function isCanListCommittee(): bool
    {
        return $this->canListCommittee;
    }

    /**
     * @return boolean
     */
    public function isCanModifyCommittee(): bool
    {
        return $this->canModifyCommittee;
    }

    /**
     * @return boolean
     */
    public function isCanListFccUser(): bool
    {
        return $this->canListFccUser;
    }

    /**
     * @return boolean
     */
    public function isCanModifyFccUser(): bool
    {
        return $this->canModifyFccUser;
    }

    /**
     * @return boolean
     */
    public function isCanListMdaUser(): bool
    {
        return $this->canListMdaUser;
    }

    /**
     * @return boolean
     */
    public function isCanModifyMdaUser(): bool
    {
        return $this->canModifyMdaUser;
    }

    /**
     * @return boolean
     */
    public function isCanListFedMda(): bool
    {
        return $this->canListFedMda;
    }

    /**
     * @return boolean
     */
    public function isCanModifyFedMda(): bool
    {
        return $this->canModifyFedMda;
    }

    /**
     * @return boolean
     */
    public function isCanListStateMda(): bool
    {
        return $this->canListStateMda;
    }

    /**
     * @return boolean
     */
    public function isCanModifyStateMda(): bool
    {
        return $this->canModifyStateMda;
    }

    /**
     * @return boolean
     */
    public function isCanManageRoles(): bool
    {
        return $this->canManageRoles;
    }

    /**
     * @return boolean
     */
    public function isCanListAssignedMdas(): bool
    {
        return $this->canListAssignedMdas;
    }

    /**
     * @return boolean
     */
    public function isCanListCommitteeMdas(): bool
    {
        return $this->canListCommitteeMdas;
    }

    /**
     * @return boolean
     */
    public function isCanConfirmFedMdaNominalRollUpload(): bool
    {
        return $this->canConfirmFedMdaNominalRollUpload;
    }

    /**
     * @return boolean
     */
    public function isCanConfirmStateMdaNominalRollUpload(): bool
    {
        return $this->canConfirmStateMdaNominalRollUpload;
    }

    /**
     * @return boolean
     */
    public function isCanUploadFedMdaNominalRoll(): bool
    {
        return $this->canUploadFedMdaNominalRoll;
    }

    /**
     * @return boolean
     */
    public function isCanUploadFedMdaVacancy(): bool
    {
        return $this->canUploadFedMdaVacancy;
    }

    /**
     * @return boolean
     */
    public function isCanViewFedMdaCareerCharacterBalancingIndex(): bool
    {
        return $this->canViewFedMdaCareerCharacterBalancingIndex;
    }

    /**
     * @return boolean
     */
    public function isCanViewFedMdaCareerPostDist(): bool
    {
        return $this->canViewFedMdaCareerPostDist;
    }

    /**
     * @return boolean
     */
    public function isCanViewFedConsolidatedCareerPostDist(): bool
    {
        return $this->canViewFedConsolidatedCareerPostDist;
    }

    /**
     * @return boolean
     */
    public function isCanViewFedComparativeDataOnStaffDist(): bool
    {
        return $this->canViewFedComparativeDataOnStaffDist;
    }

    /**
     * @return boolean
     */
    public function isCanViewFedListOfChiefExecutives(): bool
    {
        return $this->canViewFedListOfChiefExecutives;
    }

    /**
     * @return boolean
     */
    public function isCanViewFedPOLOHByPostAndYearDist(): bool
    {
        return $this->canViewFedPOLOHByPostAndYearDist;
    }

    /**
     * @return boolean
     */
    public function isCanViewFedPOLOPostDist(): bool
    {
        return $this->canViewFedPOLOPostDist;
    }

    /**
     * @return boolean
     */
    public function isCanSelectAllMdas(): bool
    {
        return $this->canSelectAllMdas;
    }

    /**
     * @return boolean
     */
    public function isCanSelectOnlyAssignedMdas(): bool
    {
        return $this->canSelectOnlyAssignedMdas;
    }

    /**
     * @return boolean
     */
    public function isCanSelectOnlyCommitteeMdas(): bool
    {
        return $this->canSelectOnlyCommitteeMdas;
    }

    /**
     * @return boolean
     */
    public function isCanSelectOnlyUserMda(): bool
    {
        return $this->canSelectOnlyUserMda;
    }

    /**
     * @return boolean
     */
    public function isCanUploadStateMdaNominalRoll(): bool
    {
        return $this->canUploadStateMdaNominalRoll;
    }

    /**
     * @return boolean
     */
    public function isCanUploadStateMdaVacancy(): bool
    {
        return $this->canUploadStateMdaVacancy;
    }

    /**
     * @return mixed
     */
    public function getCanViewFederalReports()
    {
        return $this->canViewFederalReports;
    }

    /**
     * @return mixed
     */
    public function getCanViewStateReports()
    {
        return $this->canViewStateReports;
    }

    /**
     * @return mixed
     */
    public function getCanViewReports()
    {
        return $this->canViewReports;
    }

    /**
     * @return bool
     */
    public function isCanQueryFedMdaNominalRoll(): bool
    {
        return $this->canQueryFedMdaNominalRoll;
    }

    /**
     * @return bool
     */
    public function isCanRequestFedMdaCareerCharacterBalancingIndex(): bool
    {
        return $this->canRequestFedMdaCareerCharacterBalancingIndex;
    }

    /**
     * @return bool
     */
    public function isMdaAdmin(): bool
    {
        return $this->mdaAdmin;
    }

}