<?php
/**
 * Created by PhpStorm.
 * User: IronHide
 * Date: 9/18/2016
 * Time: 10:06 AM
 */

namespace AppBundle\Utils;

use Symfony\Component\Cache\Adapter\AbstractAdapter;

class SharedDataManager
{
    private $sharedDataService;
    private $cache;

    /**
     * SharedDataManager constructor.
     * @param $sharedDataService
     */
    public function __construct(SharedDataService $sharedDataService, AbstractAdapter $cache)
    {
        $this->sharedDataService = $sharedDataService;
        $this->cache = $cache;
    }

    public function removeCacheItem($cached_item_key)
    {
        $this->cache->deleteItem($cached_item_key);
    }

    /**
     * remove all the cache items stored in the pool
     */
    public function clearCache()
    {
        $this->cache->clear();
    }

    public function getNationalityCode()
    {
        $cachedNationalityCode = $this->cache->getItem(AppConstants::KEY_CACHED_NATIONALITY_CODES);
        if (!$cachedNationalityCode->isHit()) {
            $nationalityCode = $this->sharedDataService->getNationalityCode();
            $cachedNationalityCode->set($nationalityCode);
            $this->cache->save($cachedNationalityCode);
        } else {
            $nationalityCode = $cachedNationalityCode->get();
        }

        return $nationalityCode;
    }

    public function getGeoPoliticalZones()
    {
        $cachedGeoPoliticalZones = $this->cache->getItem(AppConstants::KEY_CACHED_GEO_POLITICAL_ZONES);
        if (!$cachedGeoPoliticalZones->isHit()) {
            $geoPoliticalZones = $this->sharedDataService->getGeoPoliticalZones();
            $cachedGeoPoliticalZones->set($geoPoliticalZones);
            $this->cache->save($cachedGeoPoliticalZones);
        } else {
            $geoPoliticalZones = $cachedGeoPoliticalZones->get();
        }

        return $geoPoliticalZones;
    }

    public function getNigerianStates()
    {
        $cachedStates = $this->cache->getItem(AppConstants::KEY_CACHED_NIGERIAN_STATES);
        if (!$cachedStates->isHit()) {
            $nigerianStates = $this->sharedDataService->getNigerianStates();
            $cachedStates->set($nigerianStates);
            $this->cache->save($cachedStates);
        } else {
            $nigerianStates = $cachedStates->get();
        }

        return $nigerianStates;
    }

    public function getStateLga($stateId)
    {
        $cachedLga = $this->cache->getItem(AppConstants::KEY_CACHED_STATE_LGAS);
        if (!$cachedLga->isHit()) {

            $allStateLgas = $this->sharedDataService->loadStateLgas();
            $cachedLga->set($allStateLgas);
            $this->cache->save($cachedLga);
        }

        if (!empty($stateId)) {
            return $cachedLga->get()[$stateId];
        } else {
            return $cachedLga;
        }
    }

    public function getGender()
    {
        $cachedGender = $this->cache->getItem(AppConstants::KEY_CACHED_GENDER);
        if (!$cachedGender->isHit()) {
            $gender = array(['id' => 'M', 'description' => "Male"], ['id' => 'F', 'description' => "Female"]);
            $cachedGender->set($gender);
            $this->cache->save($cachedGender);
        } else {
            $gender = $cachedGender->get();
        }

        return $gender;
    }

    public function getCharacterBalancingReportGradeLevelCategories()
    {
        $cachedCBIReportGradeLevelCategories = $this->cache->getItem(AppConstants::KEY_CACHED_CBI_REPORT_GL_CATEGORIES);
        if (!$cachedCBIReportGradeLevelCategories->isHit()) {
            $cbiReportGradeLevelCategories = $this->sharedDataService->getCBIRecruitmentCategories();
            $cachedCBIReportGradeLevelCategories->set($cbiReportGradeLevelCategories);
            $this->cache->save($cachedCBIReportGradeLevelCategories);
        } else {
            $cbiReportGradeLevelCategories = $cachedCBIReportGradeLevelCategories->get();
        }

        return $cbiReportGradeLevelCategories;
    }

    public function getApprovalStatus()
    {
        $cachedApprovalStatus = $this->cache->getItem(AppConstants::KEY_CACHED_APPROVAL_STATUS);
        if (!$cachedApprovalStatus->isHit()) {
            $approvalStatus = array(['id' => 'APPROVED', 'description' => "APPROVED"], ['id' => 'DECLINED', 'description' => "DECLINED"]);
            $cachedApprovalStatus->set($approvalStatus);
            $this->cache->save($cachedApprovalStatus);
        } else {
            $approvalStatus = $cachedApprovalStatus->get();
        }

        return $approvalStatus;
    }

    public function getSubmissionStatus()
    {
        $cachedSubmissionStatus = $this->cache->getItem(AppConstants::KEY_CACHED_SUBMISSION_STATUS);
        if (!$cachedSubmissionStatus->isHit()) {
            $submissionStatus = array(
                ['description' => 'Failed Validation', 'status' => 'FAILED']
            , ['description' => 'Passed Validation', 'status' => 'PASSED']
            );
            $cachedSubmissionStatus->set($submissionStatus);
            $this->cache->save($cachedSubmissionStatus);
        } else {
            $submissionStatus = $cachedSubmissionStatus->get();
        }

        return $submissionStatus;
    }

    public function getFCCRoles()
    {
        $cachedFCCRoles = $this->cache->getItem(AppConstants::KEY_CACHED_FCC_ROLES);
        if (!$cachedFCCRoles->isHit()) {
            $fccRoles = $this->sharedDataService->getFCCRoles();
            $cachedFCCRoles->set($fccRoles);
            $this->cache->save($cachedFCCRoles);
        } else {
            $fccRoles = $cachedFCCRoles->get();
        }

        return $fccRoles;
    }

    public function getMDARoles()
    {
        $cachedMDARoles = $this->cache->getItem(AppConstants::KEY_CACHED_MDA_ROLES);
        if (!$cachedMDARoles->isHit()) {
            $mdaRoles = $this->sharedDataService->getFCCRoles();
            $cachedMDARoles->set($mdaRoles);
            $this->cache->save($cachedMDARoles);
        } else {
            $mdaRoles = $cachedMDARoles->get();
        }

        return $mdaRoles;
    }

    public function getFCCUsers()
    {
        $cachedUsers = $this->cache->getItem(AppConstants::KEY_CACHED_FCC_USERS);
        if (!$cachedUsers->isHit()) {
            $fccUsers = $this->sharedDataService->getFCCUsers();
            $cachedUsers->set($fccUsers);
            $this->cache->save($cachedUsers);
        } else {
            $fccUsers = $cachedUsers->get();
        }

        return $fccUsers;
    }

    public function getFCCUserByRole($fccPrimaryRole)
    {
        $roleUsers = array_filter($this->getFCCUsers(),
            function ($user) use ($fccPrimaryRole) {
                return ($user['primary_role'] == $fccPrimaryRole);
            });
        return $roleUsers;
    }

    public function getFCCUserByPrivilege($privilegeId)
    {
        $privilegeUsers = array_filter($this->getFCCUsers(),
            function ($user) use ($privilegeId) {
                return (in_array($privilegeId, $this->getSystemRolePrivileges()[$user['primary_role']]));
            });
        return $privilegeUsers;
    }

    public function getFCCStateDeskOfficers($stateId, $privilegeId)
    {
        $fccStateDeskOfficers = array_filter($this->getFCCUsers(),
            function ($user) use ($stateId, $privilegeId) {
                return (($user['state_of_posting_id'] == $stateId) && (in_array($privilegeId, $this->getSystemRolePrivileges()[$user['primary_role']])));
            });

        return $fccStateDeskOfficers;
    }

    public function getCommittees()
    {
        $cachedCommittees = $this->cache->getItem(AppConstants::KEY_CACHED_COMMITTEES);
        if (!$cachedCommittees->isHit()) {
            $committees = $this->sharedDataService->getCommittees();
            $cachedCommittees->set($committees);
            $this->cache->save($cachedCommittees);
        } else {
            $committees = $cachedCommittees->get();
        }

        return $committees;
    }

    public function getOrganizations()
    {
        $cachedOrganizations = $this->cache->getItem(AppConstants::KEY_CACHED_ORGANIZATIONS);
        if (!$cachedOrganizations->isHit()) {
            $organizations = $this->sharedDataService->getOrganizations();
            $cachedOrganizations->set($organizations);
            $this->cache->save($cachedOrganizations);
        } else {
            $organizations = $cachedOrganizations->get();
        }

        return $organizations;
    }


    public function getOrganizationById($establishmentId)
    {
        $organizations = array_filter($this->getOrganizations(),
            function ($organization) use ($establishmentId) {
                return ($organization['id'] == $establishmentId);
            });

        return $organizations;
    }


    public function getOrganizationByType($establishmentType)
    {
        $organizations = array_filter($this->getOrganizations(),
            function ($organization) use ($establishmentType) {
                return ($organization['establishment_type_id'] == $establishmentType);
            });

        return $organizations;
    }

    public function getOrganizationByType2($needle, $fetchDemoAgencies = true)
    {
        $organizations = array_filter($this->getOrganizations(),
            function ($organization) use ($needle, $fetchDemoAgencies) {
                if ($fetchDemoAgencies) {
                    return (strpos($organization['establishment_type_id'], $needle) == 0);
                }else{
                    return (strpos($organization['establishment_type_id'], $needle) == 0 && $organization['establishment_code'] >= 1000);
                }
            });

        return $organizations;
    }

    public function getFederalOrganizationsForOpenData($needle, $fetchDemoAgencies)
    {
        $organizations = $this->getOrganizationByType2($needle, $fetchDemoAgencies);

        $openDataOrganizations = array();
        foreach ($organizations as $organization){
            $openDataOrganizations[] = $organization;
        }

        $totalProcessed  = array_column($openDataOrganizations, 'total_submissions_processed');
        $mdaName = array_column($openDataOrganizations, 'organization_name');

        array_multisort($totalProcessed, SORT_DESC, $mdaName, SORT_ASC, $openDataOrganizations);

        return $openDataOrganizations;
    }

    public function getOrganizationByLevelOfGovernment($levelOfGovernment)
    {
        $organizations = array_filter($this->getOrganizations(),
            function ($organization) use ($levelOfGovernment) {
                return ($organization['level_of_government'] == $levelOfGovernment);
            });

        return $organizations;
    }

    public function getOrganizationByType2AndCommittee($organizationType, $committee)
    {
        $organizations = array_filter($this->getOrganizations(),
            function ($organization) use ($organizationType, $committee) {
                return ((strpos($organization['establishment_type_id'], $organizationType) == 0) && $organization['fcc_committee_id'] == $committee);
            });

        return $organizations;
    }

    public function getOrganizationByType2AndDeskOfficer($organizationType, $deskOfficerUserId)
    {
        $organizations = array_filter($this->getOrganizations(),
            function ($organization) use ($organizationType, $deskOfficerUserId) {
                return ((strpos($organization['establishment_type_id'], $organizationType) == 0) && $organization['fcc_desk_officer_id'] == $deskOfficerUserId);
            });

        return $organizations;
    }

    public function getStateMinistriesByStateOwner($stateId)
    {
        $organizations = array_filter($this->getOrganizations(),
            function ($organization) use ($stateId) {
                return (($organization['state_owned_establishment_state_id'] == $stateId)
                    && ($organization['establishment_type_id'] == AppConstants::STATE_MINISTRY_ESTABLISHMENT));
            });

        return $organizations;
    }

    public function getDepartments()
    {
        $cachedDepartments = $this->cache->getItem(AppConstants::KEY_CACHED_DEPARTMENTS);
        if (!$cachedDepartments->isHit()) {
            $departments = $this->sharedDataService->getDepartments();
            $cachedDepartments->set($departments);
            $this->cache->save($cachedDepartments);
        } else {
            $departments = $cachedDepartments->get();
        }

        return $departments;
    }

    public function getClientOrganization()
    {
        $cachedClientOrganization = $this->cache->getItem(AppConstants::KEY_CACHED_CLIENT_ORGANIZATION);
        if (!$cachedClientOrganization->isHit()) {
            $clientOrganization = $this->sharedDataService->getClientOrganization();
            $cachedClientOrganization->set($clientOrganization);
            $this->cache->save($cachedClientOrganization);
        } else {
            $clientOrganization = $cachedClientOrganization->get();
        }

        return $clientOrganization;
    }

    public function getSubmissionYears()
    {
        $cachedSubmissionYears = $this->cache->getItem(AppConstants::KEY_CACHED_SUBMISSION_YEARS);
        if (!$cachedSubmissionYears->isHit()) {
            $submissionYears = $this->sharedDataService->getSubmissionYears();
            $cachedSubmissionYears->set($submissionYears);
            $this->cache->save($cachedSubmissionYears);
        } else {
            $submissionYears = $cachedSubmissionYears->get();
        }

        return $submissionYears;
    }

    public function getDownloadCategories()
    {
        $cachedDownloadCategories = $this->cache->getItem(AppConstants::KEY_CACHED_DOWNLOAD_CATEGORIES);
        if (!$cachedDownloadCategories->isHit()) {
            $downloadCategories = $this->sharedDataService->getDownloadCategories();
            $cachedDownloadCategories->set($downloadCategories);
            $this->cache->save($cachedDownloadCategories);
        } else {
            $downloadCategories = $cachedDownloadCategories->get();
        }

        return $downloadCategories;
    }

    public function getSystemPrivileges()
    {
        $cachedSystemPrivileges = $this->cache->getItem(AppConstants::KEY_CACHED_SYSTEM_PRIVILEGES);
        if (!$cachedSystemPrivileges->isHit()) {
            $systemPrivileges = $this->sharedDataService->getSystemPrivileges();
            $cachedSystemPrivileges->set($systemPrivileges);
            $this->cache->save($cachedSystemPrivileges);
        } else {
            $systemPrivileges = $cachedSystemPrivileges->get();
        }

        return $systemPrivileges;
    }

    public function getSystemRolePrivileges()
    {
        $cachedSystemRolePrivileges = $this->cache->getItem(AppConstants::KEY_CACHED_SYSTEM_ROLE_PRIVILEGES);
        if (!$cachedSystemRolePrivileges->isHit()) {
            $systemRolePrivileges = $this->sharedDataService->getSystemRolePrivileges();
            $cachedSystemRolePrivileges->set($systemRolePrivileges);
            $this->cache->save($cachedSystemRolePrivileges);
        } else {
            $systemRolePrivileges = $cachedSystemRolePrivileges->get();
        }

        return $systemRolePrivileges;
    }

    public function getSystemRoleCategories()
    {
        $cachedSystemRoleCategories = $this->cache->getItem(AppConstants::KEY_CACHED_ROLE_CATEGORIES);
        if (!$cachedSystemRoleCategories->isHit()) {
            $roleCategories = $this->sharedDataService->getRoleCategories();
            $cachedSystemRoleCategories->set($roleCategories);
            $this->cache->save($cachedSystemRoleCategories);
        } else {
            $roleCategories = $cachedSystemRoleCategories->get();
        }

        return $roleCategories;
    }

    public function getStaticContent($contentCode)
    {
        $cachedStaticContent = $this->cache->getItem(AppConstants::KEY_CACHED_STATIC_CONTENT);
        if (!$cachedStaticContent->isHit()) {
            $staticContents = $this->sharedDataService->getStaticContent();
            $cachedStaticContent->set($staticContents);
            $this->cache->save($cachedStaticContent);
        } else {
            $staticContents = $cachedStaticContent->get();
        }

        $contentValue = '';
        if (array_key_exists($contentCode, $staticContents)) {
            $contentValue = $staticContents[$contentCode];
        }

        return $contentValue;
    }

    public function getCareerGradeLevels()
    {
        $cachedCareerGradeLevels = $this->cache->getItem(AppConstants::KEY_CACHED_CAREER_CIVIL_SERVANT_GRADE_LEVELS);
        if (!$cachedCareerGradeLevels->isHit()) {
            $careerGradeLevels = $this->sharedDataService->getCareerGradeLevels();
            $cachedCareerGradeLevels->set($careerGradeLevels);
            $this->cache->save($cachedCareerGradeLevels);
        } else {
            $careerGradeLevels = $cachedCareerGradeLevels->get();
        }
        return $careerGradeLevels;
    }

    public function getPoliticalGradeLevels()
    {
        $cachedPolGradeLevels = $this->cache->getItem(AppConstants::KEY_CACHED_POLITICAL_OFFICE_GRADE_LEVELS);
        if (!$cachedPolGradeLevels->isHit()) {
            $politicalGradeLevels = $this->sharedDataService->getPoliticalGradeLevels();
            $cachedPolGradeLevels->set($politicalGradeLevels);
            $this->cache->save($cachedPolGradeLevels);
        } else {
            $politicalGradeLevels = $cachedPolGradeLevels->get();
        }
        return $politicalGradeLevels;
    }

    public function getMaritalStatus()
    {
        $cachedMaritalStatus = $this->cache->getItem(AppConstants::KEY_CACHED_MARITAL_STATUS);
        if (!$cachedMaritalStatus->isHit()) {
            $maritalStatus = $this->sharedDataService->getMaritalStatus();
            $cachedMaritalStatus->set($maritalStatus);
            $this->cache->save($cachedMaritalStatus);
        } else {
            $maritalStatus = $cachedMaritalStatus->get();
        }
        return $maritalStatus;
    }

    public function getOrganizationCategoryTypes()
    {
        $cachedOrganizationCategoryTypes = $this->cache->getItem(AppConstants::KEY_CACHED_ORGANIZATION_CATEGORY_TYPES);
        if (!$cachedOrganizationCategoryTypes->isHit()) {
            $organizationCategoryTypes = $this->sharedDataService->getOrganizationCategoryTypes();
            $cachedOrganizationCategoryTypes->set($organizationCategoryTypes);
            $this->cache->save($cachedOrganizationCategoryTypes);
        } else {
            $organizationCategoryTypes = $cachedOrganizationCategoryTypes->get();
        }
        return $organizationCategoryTypes;
    }

}