<?php 

/**
 * @return \Symfony\Component\HttpFoundation\Request
 */
function request() {
    return \Symfony\Component\HttpFoundation\Request::createFromGlobals();
}

function redirect($path, $extra = []) {
    $response = \Symfony\Component\HttpFoundation\Response::create(null, \Symfony\Component\HttpFoundation\Response::HTTP_FOUND, ['Location' => $path]);
    if (key_exists('cookies', $extra)) {
        foreach ($extra['cookies'] as $cookie) {
            $response->headers->setCookie($cookie);
        }
    }
    $response->send();
    exit;
}

/* ========================== */
/* Users functions            */
/* ========================== */
function findUserByEmail($email) {
    global $db;
    
    try {
        $query = "SELECT * FROM Users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (\Exception $e) {
        throw $e;
    }
}

function findUserById($user_id) {
    global $db;
    
    try {
        $query = "SELECT * FROM Users WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (\Exception $e) {
        throw $e;
    }
}

function findUserByAccessToken() {
    global $db;
    
    try {
        $userId = decodeJwt('sub');
    } catch (\Exception $e) {
        throw $e;
    }
    
    try {
        $query = "SELECT * FROM Users WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $user['userId'] = $userId;
        return $user;
    } catch (\Exception $e) {
        throw $e;
    }
}

function createUser($user_id,$hoh_id,$pm_id,$user_name,$user_email,$user_type, $password) {
    global $db;
    
    try {
        $query = "INSERT INTO Users (user_id,hoh_id,pm_id,user_name, email, password, role_id) VALUES (:user_id, :hoh_id, :pm_id, :name, :email, :password, :type)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':hoh_id', $hoh_id);
        $stmt->bindParam(':pm_id', $pm_id);
        $stmt->bindParam(':name', $user_name);
        $stmt->bindParam(':email', $user_email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':type', $user_type);
        $stmt->execute();
        $user_id = $db->lastInsertId();
        
        return findUserById($user_id);
    } catch (\Exception $e) {
        throw $e;
    }
}

function updateUser($hoh_id, $user_id, $user_name, $role_id) {
    global $db;
    
    try {
        $query = 'UPDATE Users SET hoh_id = :hoh_id, user_name = :user_name, user_phone = :user_phone, role_id = :role_id WHERE user_id = :user_id';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hoh_id', $hoh_id);
        $stmt->bindParam(':user_name', $user_name);
        $stmt->bindParam(':user_phone', $user_phone);
        $stmt->bindParam(':role_id', $role_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    } catch (\Exception $e) {
        error_log("Failed!" . var_dump($e) , 0);
        return false;
    }
    
    return true;
}

function logUser($user_id,$last_log) {
    global $db;
    
    try {
        $query = 'UPDATE Users SET last_log = :last_log WHERE user_id = :user_id';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':last_log', $last_log);
        $stmt->execute();
    } catch (\Exception $e) {
        error_log("Failed!" . var_dump($e) , 0);
        return false;
    }
    
    return true;
}

function updatePassword($password, $userId) {
    global $db;
    
    try {
        $query = 'UPDATE Users SET password = :password WHERE user_id = :userId';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
    } catch (\Exception $e) {
        return false;
    }
    
    return true;
}

/* ================================= */
/* Property Manager Functions        */
/* ================================= */
function getPMProperties($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM PropertyListing WHERE pm_id = :user_id ORDER BY property_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $pm_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $pm_properties;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getPropertyManager($pm_id) {
    global $db;
    
    try {
        $query = "SELECT * FROM PropertyManager WHERE pm_id = :pm_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':pm_id', $pm_id);
        $stmt->execute();
        $manager = $stmt->fetch(PDO::FETCH_ASSOC);
        return $manager;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getPMProperty($unit_id) {
    global $db;
    
    try {
        $query = "SELECT * FROM PropertyListing WHERE unit_id = :unit_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':unit_id', $unit_id);
        $stmt->execute();
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        return $property;
    } catch (\Exception $e) {
        throw $e;
    }
}

function addPropertyListing($pm_id,$property) {
    global $db;
  
    try {
        $query = "SELECT * FROM PropertyListing WHERE pm_id = :pm_id AND unit_id = :unit_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':unit_id', $property["unit_id"]);
        $stmt->bindParam(':pm_id', $pm_id);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO PropertyListing 
                             (unit_id, pm_id, property_name, property_type, property_status, addr_street_number, addr_street_name, addr_city, addr_state, addr_zip, neighborhood, unit_number, bin_number available_date, fee_paid_by_owner, price, beds, split, baths, rent_includes, heat_source, unit_level, parking, square_footage, tenant_contact, pet_policy, private_description, features)
                      VALUES (:unit_id, :pm_id, :property_name, :property_type, :property_status, :addr_street_number, :addr_street_name, :addr_city, :addr_state, :addr_zip, :neighborhood, :unit_number, :bin_number :available_date, :fee_paid_by_owner, :price, :beds, :split, :baths, :rent_includes, :heat_source, :unit_level, :parking, :square_footage, :tenant_contact, :pet_policy, :private_description, :features)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':unit_id', $property["unit_id"]);
            $stmt->bindParam(':pm_id', $pm_id);
            $stmt->bindParam(':property_name', $property["property_name"]);
            $stmt->bindParam(':property_type', $property["property_type"]);
            $stmt->bindParam(':property_status', $property["property_status"]);
            $stmt->bindParam(':addr_street_number', $property["addr_street_number"]);
            $stmt->bindParam(':addr_street_name', $property["addr_street_name"]);
            $stmt->bindParam(':addr_city', $property["addr_city"]);
            $stmt->bindParam(':addr_state', $property["addr_state"]);
            $stmt->bindParam(':addr_zip', $property["addr_zip"]);
            $stmt->bindParam(':neighborhood', $property["neighborhood"]);
            $stmt->bindParam(':unit_number', $property["unit_number"]);
            $stmt->bindParam(':bin_number', $property["bin_number"]);
            $stmt->bindParam(':available_date', $property["available_date"]);
            $stmt->bindParam(':fee_paid_by_owner', $property["fee_paid_by_owner"]);
            $stmt->bindParam(':price', $property["price"]);
            $stmt->bindParam(':beds', $property["beds"]);
            $stmt->bindParam(':split', $property["split"]);
            $stmt->bindParam(':baths', $property["baths"]);
            $stmt->bindParam(':rent_includes', $property["rent_includes"]);
            $stmt->bindParam(':heat_source', $property["heat_source"]);
            $stmt->bindParam(':unit_level', $property["unit_level"]);
            $stmt->bindParam(':parking', $property["parking"]);
            $stmt->bindParam(':square_footage', $property["square_footage"]);
            $stmt->bindParam(':tenant_contact', $property["tenant_contact"]);
            $stmt->bindParam(':pet_policy', $property["pet_policy"]);
            $stmt->bindParam(':private_description', $property["private_description"]);
            $stmt->bindParam(':features', $property["features"]);            
            $stmt->execute();
        } else {
            $query = "UPDATE PropertyListing SET property_name = :property_name, property_type = :property_type, property_status = :property_status, addr_street_number = :addr_street_number, addr_street_name = :addr_street_name, addr_city = :addr_city, addr_state = :addr_state, addr_zip = :addr_zip, neighborhood = :neighborhood, unit_number = :unit_number, bin_number = :bin_number, available_date = :available_date, fee_paid_by_owner = :fee_paid_by_owner, price = :price, beds = :beds, split = :split, baths = :baths, rent_includes = :rent_includes, heat_source = :heat_source, unit_level = :unit_level, parking = :parking, square_footage = :square_footage, tenant_contact = :tenant_contact, pet_policy = :pet_policy, private_description = :private_description, features = :features WHERE unit_id = :unit_id AND pm_id = :pm_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':unit_id', $property["unit_id"]);
            $stmt->bindParam(':pm_id', $pm_id);
            $stmt->bindParam(':property_name', $property["property_name"]);
            $stmt->bindParam(':property_type', $property["property_type"]);
            $stmt->bindParam(':property_status', $property["property_status"]);
            $stmt->bindParam(':addr_street_number', $property["addr_street_number"]);
            $stmt->bindParam(':addr_street_name', $property["addr_street_name"]);
            $stmt->bindParam(':addr_city', $property["addr_city"]);
            $stmt->bindParam(':addr_state', $property["addr_state"]);
            $stmt->bindParam(':addr_zip', $property["addr_zip"]);
            $stmt->bindParam(':neighborhood', $property["neighborhood"]);
            $stmt->bindParam(':unit_number', $property["unit_number"]);
            $stmt->bindParam(':bin_number', $property["bin_number"]);
            $stmt->bindParam(':available_date', $property["available_date"]);
            $stmt->bindParam(':fee_paid_by_owner', $property["fee_paid_by_owner"]);
            $stmt->bindParam(':price', $property["price"]);
            $stmt->bindParam(':beds', $property["beds"]);
            $stmt->bindParam(':split', $property["split"]);
            $stmt->bindParam(':baths', $property["baths"]);
            $stmt->bindParam(':rent_includes', $property["rent_includes"]);
            $stmt->bindParam(':heat_source', $property["heat_source"]);
            $stmt->bindParam(':unit_level', $property["unit_level"]);
            $stmt->bindParam(':parking', $property["parking"]);
            $stmt->bindParam(':square_footage', $property["square_footage"]);
            $stmt->bindParam(':tenant_contact', $property["tenant_contact"]);
            $stmt->bindParam(':pet_policy', $property["pet_policy"]);
            $stmt->bindParam(':private_description', $property["private_description"]);
            $stmt->bindParam(':features', $property["features"]);            
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }
}

function getPM3rdVerifications($unit_id) {
    global $db;
    
    return 3;
}

function getPMDocsAdded($unit_id) {
    global $db;
    
    return 3;
}

function getPMDemoCompleted($unit_id) {
    global $db;
    
    try {
        $query = "SELECT count(*) as form_count FROM MemberForms WHERE form_name='HouseholdDemographics' AND percent_complete=100 AND user_id IN (SELECT user_id FROM MemberInvite where pm_id = :pm_id)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':pm_id', $unit_id);
        $stmt->execute();
        $mem_forms = $stmt->fetch(PDO::FETCH_ASSOC);
        return $mem_forms["form_count"];
    } catch (\Exception $e) {
        throw $e;
    }
}

function getPMPreScreen($unit_id) {
    global $db;
    
    return 3;
}

function getPMIntakes($unit_id) {
    global $db;
    
    return 3;
}

function getPMVerifications($unit_id) {
    global $db;
    
    return 4;
}

/* ================================= */
/* MemberInvite functions            */
/* ================================= */
function findInviteByPasscode($user_passcode) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberInvite WHERE tenant_passcode = :tenant_passcode";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tenant_passcode', $user_passcode);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        throw $e;
    }
}

function getMemberInvite($userId) {
    global $db;
  
    try {
        $query = "SELECT * FROM MemberInvite WHERE mi_id = :mi_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':mi_id', $userId);
        $stmt->execute();
        $invite = $stmt->fetch(PDO::FETCH_ASSOC);
        return $invite;
    } catch (\Exception $e) {
        throw $e;
    }
    return 0;
}

function getAllHOHInvites($userId) {
    global $db;
  
    try {
        $query = "SELECT * FROM MemberInvite WHERE pm_id = :pm_id and mi_id=hoh_id ORDER BY hoh_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':pm_id', $userId);
        $stmt->execute();
        $invites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $invites;
    } catch (\Exception $e) {
        throw $e;
    }
    return 0;
}

function getAllHouseholdInvites($userId) {
    global $db;
  
    try {
        $query = "SELECT * FROM MemberInvite WHERE hoh_id = :hoh_id ORDER BY mi_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hoh_id', $userId);
        $stmt->execute();
        $invites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $invites;
    } catch (\Exception $e) {
        throw $e;
    }
    return 0;
}

function getHohProperty($userId) {
    global $db;
  
    try {
        $query = "SELECT property_id FROM MemberInvite WHERE hoh_id = :hoh_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':hoh_id', $userId);
        $stmt->execute();
        $invite = $stmt->fetch(PDO::FETCH_ASSOC);
        return $invite["property_id"];
    } catch (\Exception $e) {
        throw $e;
    }
    return 0;
}

function addMemberInvite($pm_id,$hoh_id,$invite) {
    global $db;
  
    $last_id = '';
    
    try {
        $query = "SELECT mi_id FROM MemberInvite WHERE hoh_id=" . $hoh_id . " AND tenant_surname='" . $invite["tenant_surname"] . "' AND tenant_forename='" . $invite["tenant_forename"] . "' AND tenant_email='" . $invite["tenant_email"] . "'";
        var_dump($query);
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberInvite (mi_id, pm_id, hoh_id, property_id, tenant_surname, tenant_forename, tenant_email, tenant_phone, tenant_passcode) VALUES (NULL, $pm_id, '" . $hoh_id . "', '" . $invite["tenant_property"] . "', '" . $invite["tenant_surname"] . "', '" . $invite["tenant_forename"] . "', '" . $invite["tenant_email"] . "', '" . $invite["tenant_phone"] . "', '" . $invite["tenant_passcode"] . "')";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $last_id = $db->lastInsertId();
            
            if ($hoh_id == 0) {
                $query = "UPDATE MemberInvite SET hoh_id='" . $last_id . " WHERE mi_id =" . $last_id;
                $stmt = $db->prepare($query);
                $stmt->execute();
            }
        } else {    
            $q_invite = $stmt->fetch(PDO::FETCH_ASSOC);
            $last_id = $q_invite["mi_id"];
            $query = "UPDATE MemberInvite SET tenant_surname='" . $invite["tenant_surname"] . "', tenant_forename='" . $invite["tenant_forename"] . "', tenant_email='" . $invite["tenant_email"] . "', tenant_phone= '" . $invite["tenant_email"] . "' WHERE mi_id =" . $last_id;
            $stmt = $db->prepare($query);
            $stmt->execute();
        }
        
        $query = "SELECT * FROM MemberInvite WHERE mi_id = :mi_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':mi_id', $last_id);
        $stmt->execute();
        $invite = $stmt->fetch(PDO::FETCH_ASSOC);
        return $invite;
    } catch (\Exception $e) {
        throw $e;
    }
    
    return 0;
}

/* ================================= */
/* Resident Question Functions       */
/* ================================= */
function getResidentQuestions($data_category) {
    global $db;
    
    try {
        $query = "SELECT * FROM ResidentQuestions WHERE data_catagory = :data_category ORDER BY data_position";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':data_category', $data_category);
        $stmt->execute();
        $pm_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $pm_properties;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getAllMemberDemographics($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberDemographics WHERE head_house = :user_id and user_id <> :user_id ORDER BY user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_demos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_demos;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getNextMemberDemographics($userId,$memberNum) {
    global $db;
   
    try {
        $query = "SELECT * FROM MemberDemographics WHERE head_house = :user_id and user_id <> :user_id LIMIT $memberNum,1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_demos = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_demos;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getMemberDemographics($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberDemographics WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_demos = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_demos;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getAllHousehold($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberDemographics WHERE head_house = :head_house AND user_id <> :user_id ORDER BY relationship";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':head_house', $userId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_demos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_demos;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getHouseholdMembersNum($userId) {
    global $db;
    
    try {
        $query = "SELECT house_mems FROM MemberDemographics WHERE user_id = :user_id ORDER BY relationship";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $house_mems = $stmt->fetch(PDO::FETCH_ASSOC);
        return $house_mems;
    } catch (\Exception $e) {
        throw $e;
    }
}

function addMemberDemographics($type,$user_id,$lastName,$firstName,$middleName,$birthDate,$hoh,$disabled,$racial,$ethnic,$governmentID,$pregnant,$marital,$caregiver,$student,$relationship) {
    global $db;
    
    $gov_type = "SSN";
    
    /* Might be able to use REPLACE INTO, but for now lets use SELECT INSERT/UPDATE */ 
    $racial = substr($racial, -1);
    $ethnic = substr($ethnic, -1);
    $birthDate = date("Y-m-d", strtotime($birthDate));

    try {
        $query = "SELECT * FROM MemberDemographics WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $query = "UPDATE MemberDemographics SET gov_type = :gov_type, gov_number = :gov_number, head_house = :head_house, surname = :surname, forename = :forename, mid_name = :mid_name, birth_date = :birth_date, race = :race, ethnicity = :ethnicity, disability = :disability, pregnant = :pregnant, marital_status = :marital_status, caregiver = :caregiver, student = :student, relationship = :relationship WHERE user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':gov_type', $gov_type);
            $stmt->bindParam(':gov_number', $governmentID);
            $stmt->bindParam(':head_house', $hoh);
            $stmt->bindParam(':surname', $lastName);
            $stmt->bindParam(':forename', $firstName);
            $stmt->bindParam(':mid_name', $middleName);
            $stmt->bindParam(':birth_date', $birthDate);
            $stmt->bindParam(':race', $racial);
            $stmt->bindParam(':ethnicity', $ethnic);
            $stmt->bindParam(':disability', $disabled);
            $stmt->bindParam(':pregnant', $pregnant);
            $stmt->bindParam(':marital_status', $marital);
            $stmt->bindParam(':caregiver', $caregiver);
            $stmt->bindParam(':student', $student);                
            $stmt->bindParam(':relationship', $relationship);                
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        } else {
            $query = "INSERT INTO MemberDemographics (user_id,gov_type, gov_number, head_house, surname, forename, mid_name, birth_date, race, ethnicity, disability, pregnant, marital_status, caregiver, student, relationship) VALUES (:user_id, :gov_type, :gov_number, :head_house, :surname, :forename, :mid_name, :birth_date, :race, :ethnicity, :disability, :pregnant, :marital_status, :caregiver, :student, :relationship)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':gov_type', $gov_type);
            $stmt->bindParam(':gov_number', $governmentID);
            $stmt->bindParam(':head_house', $hoh);
            $stmt->bindParam(':surname', $lastName);
            $stmt->bindParam(':forename', $firstName);
            $stmt->bindParam(':mid_name', $middleName);
            $stmt->bindParam(':birth_date', $birthDate);
            $stmt->bindParam(':race', $racial);
            $stmt->bindParam(':ethnicity', $ethnic);
            $stmt->bindParam(':disability', $disabled);
            $stmt->bindParam(':pregnant', $pregnant);
            $stmt->bindParam(':marital_status', $marital);
            $stmt->bindParam(':caregiver', $caregiver);
            $stmt->bindParam(':student', $student);                
            $stmt->bindParam(':relationship', $relationship);                
            $stmt->execute();
            $user_id = $db->lastInsertId();
        }
    } catch (\Exception $e) {
        throw $e;
    }
        

    /* Check to see if the forms are available */
    if ($type == 1) {
        addMemberForms($user_id, 'HouseholdDemographics');
    }

    if ($pregnant == 'yes') {
        addMemberForms($user_id,'AffidavitPregnancy');
    } else {
        deleteMemberForms($user_id,'AffidavitPregnancy');
    }

    # Caregiver = yes
    if ($caregiver == 'yes') {
        addMemberForms($user_id,'R8LiveInCare');
    } else {
        deleteMemberForms($user_id,'R8LiveInCare');
    }

    # Student = yes
    if ($student == 'yes') {
        addMemberForms($user_id,'AnnualStudent');
    } else {
        deleteMemberForms($user_id,'AnnualStudent');
    }

    # if over 18 do demographic
    $age = getAge($birthDate);
    
    if ($age > 17) {
        addMemberForms($user_id,'TenantIncome');
        addMemberForms($user_id,'TenantAssets');
        addMemberForms($user_id,'TenantTIC');
    } else {
        deleteMemberForms($user_id,'TenantIncome');
        deleteMemberForms($user_id,'TenantAssets');
        deleteMemberForms($user_id,'TenantTIC');
    }
    
    return $user_id;
}

function calcHouseComposition($hoh) {
    global $db;
    $num_adult = 0;
    $num_child = 0;    
    $dep_string = '';
    $return_str = '';
    $age        = 0;
    
    try {
        $query = "SELECT birth_date FROM MemberDemographics WHERE head_house = :head_house ORDER BY user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':head_house', $hoh);
        $stmt->execute();
        $member_demos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($member_demos as $member_demo) {
            $age = getAge($member_demo["birth_date"]); 
            if ( $age > 17) { $num_adult++; }
            else { $num_child++; }
        }
        
        if ($num_adult > 1)      { $return_str .= $num_adult . ' Adults'; }
        else                     { $return_str .= $num_adult . ' Adult'; }

        if ($num_child > 1)      { $return_str .= ', ' . $num_child . ' Dependents'; }
        elseif ($num_child == 1) { $return_str .= ', ' . $num_child . ' Dependent'; }
        
        return $return_str;
    } catch (\Exception $e) {
        throw $e;
    }
}

/* ========================== */
/* MemberForms functions      */
/* ========================== */
function getMemberForms($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberForms WHERE user_id = :user_id ORDER BY form_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_forms;
    } catch (\Exception $e) {
        throw $e;
    }
}

function deleteMemberForms($userId,$formName) {
    global $db;
    
    try {
        $query = "DELETE FROM MemberForms WHERE user_id = :user_id and form_name = :form_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':form_name', $formName);
        $stmt->execute();
    } catch (\Exception $e) {
        throw $e;
    }
}

function addMemberForms($userId,$formName) {
    global $db;

    try {
        $query = "SELECT * FROM MemberForms WHERE user_id = :user_id AND form_name = :form_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':form_name', $formName);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberForms (user_id,form_name) VALUES (:user_id, :formName)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':formName', $formName);
            $stmt->execute();
            
            $query = "SELECT * FROM MemberForms WHERE user_id = :user_id ORDER BY form_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $member_forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $member_forms;
        } else {
            $member_forms = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $member_forms;
        }
    } catch (\Exception $e) {
        throw $e;
    }
}

function updateMemberForms($userId,$formName,$percent) {
    global $db;
    
    try {
        $query = "UPDATE MemberForms SET percent_complete = :percent_complete WHERE user_id = :user_id and form_name = :form_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':percent_complete', $percent);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':form_name', $formName);
        $stmt->execute();
    } catch (\Exception $e) {
        throw $e;
    }
}

/* =========================== */
/* MemberIncome functions      */
/* =========================== */
function getMemberIncome($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberIncome WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_income = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_income;
    } catch (\Exception $e) {
        throw $e;
    }
}

function insertMemberIncome($userId,$mem_income) {
    global $db;
    
    try {
        $query = "REPLACE INTO MemberIncome 
                      (user_id,tax_return,joint_tax_return,student,student_status,student_school,wages,wages_jobs,wages_jobs_num,employer_name1,employer_name2,employer_name3,employer_name4,employer_name5,never_employed,employed_now,work_end_date,current_employer,applied_benefits,applied_date,applied_start,not_apply_explain,receive_benefits,gross_weekly_ben,pension_va,gross_month_va,social_security,gross_month_ss,disability,gross_month_dis,child_alimony,gross_month_child,benefits_other,gross_month_bother,tanf,gross_month_tanf,family_friends,gross_month_fam,ben_other_income1,gross_month_ben_o1,ben_other_income2,gross_month_ben_o2,other_income,gross_month_other,other_ben_paid_by,ext_assist_rent,month_assist_rent,ext_assist_utils,month_assist_utils,ext_assist_phone,month_assist_phone,ext_assist_house,month_assist_house,ext_assist_trans,month_assist_trans,ext_assist_nonf,month_assist_nonf,ext_assist_other,month_assist_other,other_paid_by,no_income_explain,new_employer_name,new_employer_start_date,new_employer_gross_monthly)
                  VALUES (:user_id,:tax_return,:joint_tax_return,:student,:student_status,:student_school,:wages,:wages_jobs,:wages_jobs_num,:employer_name1,:employer_name2,:employer_name3,:employer_name4,:employer_name5,:never_employed,:employed_now,:work_end_date,:current_employer,:applied_benefits,:applied_date,:applied_start,:not_apply_explain,:receive_benefits,:gross_weekly_ben,:pension_va,:gross_month_va,:social_security,:gross_month_ss,:disability,:gross_month_dis,:child_alimony,:gross_month_child,:benefits_other,:gross_month_bother,:tanf,:gross_month_tanf,:family_friends,:gross_month_fam,:ben_other_income1,:gross_month_ben_o1,:ben_other_income2,:gross_month_ben_o2,:other_income,:gross_month_other,:other_ben_paid_by,:ext_assist_rent,:month_assist_rent,:ext_assist_utils,:month_assist_utils,:ext_assist_phone,:month_assist_phone,:ext_assist_house,:month_assist_house,:ext_assist_trans,:month_assist_trans,:ext_assist_nonf,:month_assist_nonf,:ext_assist_other,:month_assist_other,:other_paid_by,:no_income_explain,:new_employer_name,:new_employer_start_date,:new_employer_gross_monthly)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':tax_return', $mem_income["taxReturn"]);
        $stmt->bindParam(':joint_tax_return', $mem_income["jointTaxReturn"]);
        $stmt->bindParam(':student', $mem_income["student"]);
        $stmt->bindParam(':student_status', $mem_income["studentStatus"]);
        $stmt->bindParam(':student_school', $mem_income["studentSchool"]);
        $stmt->bindParam(':wages', $mem_income["wages"]);
        $stmt->bindParam(':wages_jobs', $mem_income["wagesJobs"]);
        $stmt->bindParam(':wages_jobs_num', $mem_income["wagesJobsNumber"]);
        $stmt->bindParam(':employer_name1', $mem_income["employerName1"]);
        $stmt->bindParam(':employer_name2', $mem_income["employerName2"]);
        $stmt->bindParam(':employer_name3', $mem_income["employerName3"]);
        $stmt->bindParam(':employer_name4', $mem_income["employerName4"]);
        $stmt->bindParam(':employer_name5', $mem_income["employerName5"]);
        $stmt->bindParam(':never_employed', $mem_income["neverEmployed"]);
        $stmt->bindParam(':employed_now', $mem_income["employedNow"]);
        $stmt->bindParam(':work_end_date', $mem_income["workEndDate"]);
        $stmt->bindParam(':current_employer', $mem_income["currentEmployerName"]);
        $stmt->bindParam(':applied_benefits', $mem_income["appliedForBenefits"]);
        $stmt->bindParam(':applied_date', $mem_income["dateApplied"]);
        $stmt->bindParam(':applied_start', $mem_income["dateStart"]);
        $stmt->bindParam(':not_apply_explain', $mem_income["didNotApplyExplanation"]);
        $stmt->bindParam(':receive_benefits', $mem_income["receivingBenefits"]);
        $stmt->bindParam(':gross_weekly_ben', $mem_income["grossWeeklyBenefit"]);
        $stmt->bindParam(':pension_va', $mem_income["pensionVA"]);
        $stmt->bindParam(':gross_month_va', $mem_income["grossMonthlyPensionVA"]);
        $stmt->bindParam(':social_security', $mem_income["socialSecurity"]);
        $stmt->bindParam(':gross_month_ss', $mem_income["grossMonthlySocialSecurity"]);
        $stmt->bindParam(':disability', $mem_income["disability"]);
        $stmt->bindParam(':gross_month_dis', $mem_income["grossMonthlyDisability"]);
        $stmt->bindParam(':child_alimony', $mem_income["childSupportAlimony"]);
        $stmt->bindParam(':gross_month_child', $mem_income["grossMonthlyChildSupportAlimony"]);
        $stmt->bindParam(':benefits_other', $mem_income["benefitsOther"]);
        $stmt->bindParam(':gross_month_bother', $mem_income["grossMonthlyOther"]);
        $stmt->bindParam(':tanf', $mem_income["tanf"]);
        $stmt->bindParam(':gross_month_tanf', $mem_income["grossMonthlyTanf"]);
        $stmt->bindParam(':family_friends', $mem_income["familyFriends"]);
        $stmt->bindParam(':gross_month_fam', $mem_income["grossMonthlyFamilyFriends"]);
        $stmt->bindParam(':ben_other_income1', $mem_income["other1"]);
        $stmt->bindParam(':gross_month_ben_o1', $mem_income["grossMonthlyOther1"]);
        $stmt->bindParam(':ben_other_income2', $mem_income["other2"]);
        $stmt->bindParam(':gross_month_ben_o2', $mem_income["grossMonthlyOther2"]);
        $stmt->bindParam(':other_income', $mem_income["otherIncome"]);
        $stmt->bindParam(':gross_month_other', $mem_income["otherIncomeMonthlyAmount"]);
        $stmt->bindParam(':other_ben_paid_by', $mem_income["otherIncomePaidBy"]);
        
        $stmt->bindParam(':ext_assist_rent', $mem_income["rentPaidBy"]);
        $stmt->bindParam(':month_assist_rent', $mem_income["rentAmount"]);
        $stmt->bindParam(':ext_assist_utils', $mem_income["utilitiesPaidBy"]);
        $stmt->bindParam(':month_assist_utils', $mem_income["utilitiesAmount"]);
        $stmt->bindParam(':ext_assist_phone', $mem_income["phonePaidBy"]);
        $stmt->bindParam(':month_assist_phone', $mem_income["phoneAmount"]);
        $stmt->bindParam(':ext_assist_house', $mem_income["householdSuppliesPaidBy"]);
        $stmt->bindParam(':month_assist_house', $mem_income["householdSuppliesAmount"]);
        $stmt->bindParam(':ext_assist_trans', $mem_income["transportationPaidBy"]);
        $stmt->bindParam(':month_assist_trans', $mem_income["transportationAmount"]);
        $stmt->bindParam(':ext_assist_nonf', $mem_income["nonFoodPaidBy"]);
        $stmt->bindParam(':month_assist_nonf', $mem_income["nonFoodAmount"]);
        $stmt->bindParam(':ext_assist_other', $mem_income["otherPaidBy"]);
        $stmt->bindParam(':month_assist_other', $mem_income["otherAmount"]);
        $stmt->bindParam(':other_paid_by', $mem_income["noIncome"]);
        $stmt->bindParam(':no_income_explain', $mem_income["noIncomeExplanation"]);
        $stmt->bindParam(':new_employer_name', $mem_income["newEmployerName"]);
        $stmt->bindParam(':new_employer_start_date', $mem_income["newEmployerStartDate"]);
        $stmt->bindParam(':new_employer_gross_monthly', $mem_income["newEmployerGrossMonthlyIncome"]);
        $stmt->bindParam(':user_id', $mem_income["user_id"]);
        $stmt->execute();
    } catch (\Exception $e) {
        throw $e;
    }
}

/* =========================== */
/* MemberAssets functions      */
/* =========================== */
function getMemberAssets($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberAssets WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_assets = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_assets;
    } catch (\Exception $e) {
        throw $e;
    }
}

function insertMemberAssets($userId,$mem_assets) {
    global $db;
 
    /* properly format the dollar amounts */
    $mem_assets["safeDepositValue"] = str_replace('$','',$mem_assets["safeDepositValue"]);
    $mem_assets["stocksBondsValue"] = str_replace('$','',$mem_assets["stocksBondsValue"]);
    $mem_assets["realEstateValue"] = str_replace('$','',$mem_assets["realEstateValue"]);
    $mem_assets["rentalPropertyValue"] = str_replace('$','',$mem_assets["rentalPropertyValue"]);
    $mem_assets["sellPropertyValue"] = str_replace('$','',$mem_assets["sellPropertyValue"]);
    $mem_assets["sellPropertyMortgage"] = str_replace('$','',$mem_assets["sellPropertyMortgage"]);
    $mem_assets["sellPropertyExpenses"] = str_replace('$','',$mem_assets["sellPropertyExpenses"]);
    $mem_assets["contractPropertyValue"] = str_replace('$','',$mem_assets["contractPropertyValue"]);
    $mem_assets["contractPropertyMortgage"] = str_replace('$','',$mem_assets["contractPropertyMortgage"]);
    $mem_assets["contractPropertyExpenses"] = str_replace('$','',$mem_assets["contractPropertyExpenses"]);
    $mem_assets["otherAssetsValue"] = str_replace('$','',$mem_assets["otherAssetsValue"]);
    $mem_assets["cashAvailableValue"] = str_replace('$','',$mem_assets["cashAvailableValue"]);
    $mem_assets["totalAssetsValue"] = str_replace('$','',$mem_assets["totalAssetsValue"]);
    
    try {
        $query = "REPLACE INTO MemberAssets 
                         (user_id,  checking,  checking_accts_number,  checking_bank1,  checking_bank2,  checking_bank3,  checking_bank4,  checking_bank5,  savings,  savings_accts_number,  savings_bank1,  savings_bank2,  savings_bank3,  savings_bank4,  savings_bank5,  money_market,  money_market_accts_number,  money_market_bank1,  money_market_bank2,  money_market_bank3,  money_market_bank4,  money_market_bank5,  cert_deposit,  cert_deposit_accts_number,  cert_deposit_bank1,  cert_deposit_bank2,  cert_deposit_bank3,  cert_deposit_bank4,  cert_deposit_bank5,  safe_deposit,  safe_deposit_value,  stocks_bonds,  stocks_bonds_value,  pension_annuity,  real_estate_own,  real_estate_value,  rental_property_own,  rental_property_value,  sell_property,  sell_property_address,  sell_property_value,  sell_property_mortgage,  sell_property_expenses,  contract_property,  contract_property_address,  contract_property_value,  contract_property_mortgage,  contract_property_expenses,  life_insurance,  personal_property,  other_assets,  other_assets_source,  other_assets_value,  disposed_assets,  cash_available,  cash_available_value,  total_assets,  total_assets_value)
                  VALUES (:user_id, :checking, :checking_accts_number, :checking_bank1, :checking_bank2, :checking_bank3, :checking_bank4, :checking_bank5, :savings, :savings_accts_number, :savings_bank1, :savings_bank2, :savings_bank3, :savings_bank4, :savings_bank5, :money_market, :money_market_accts_number, :money_market_bank1, :money_market_bank2, :money_market_bank3, :money_market_bank4, :money_market_bank5, :cert_deposit, :cert_deposit_accts_number, :cert_deposit_bank1, :cert_deposit_bank2, :cert_deposit_bank3, :cert_deposit_bank4, :cert_deposit_bank5, :safe_deposit, :safe_deposit_value, :stocks_bonds, :stocks_bonds_value, :pension_annuity, :real_estate_own, :real_estate_value, :rental_property_own, :rental_property_value, :sell_property, :sell_property_address, :sell_property_value, :sell_property_mortgage, :sell_property_expenses, :contract_property, :contract_property_address, :contract_property_value, :contract_property_mortgage, :contract_property_expenses, :life_insurance, :personal_property, :other_assets, :other_assets_source, :other_assets_value, :disposed_assets, :cash_available, :cash_available_value, :total_assets, :total_assets_value)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':checking', $mem_assets["checking"]);
        $stmt->bindParam(':checking_accts_number', $mem_assets["checkingAcctsNumber"]);
        $stmt->bindParam(':checking_bank1', $mem_assets["checkingBank1"]);
        $stmt->bindParam(':checking_bank2', $mem_assets["checkingBank2"]);
        $stmt->bindParam(':checking_bank3', $mem_assets["checkingBank3"]);
        $stmt->bindParam(':checking_bank4', $mem_assets["checkingBank4"]);
        $stmt->bindParam(':checking_bank5', $mem_assets["checkingBank5"]);
        $stmt->bindParam(':savings', $mem_assets["savings"]);
        $stmt->bindParam(':savings_accts_number', $mem_assets["savingsAcctsNumber"]);
        $stmt->bindParam(':savings_bank1', $mem_assets["savingsBank1"]);
        $stmt->bindParam(':savings_bank2', $mem_assets["savingsBank2"]);
        $stmt->bindParam(':savings_bank3', $mem_assets["savingsBank3"]);
        $stmt->bindParam(':savings_bank4', $mem_assets["savingsBank4"]);
        $stmt->bindParam(':savings_bank5', $mem_assets["savingsBank5"]);
        $stmt->bindParam(':money_market', $mem_assets["moneyMarket"]);
        $stmt->bindParam(':money_market_accts_number', $mem_assets["moneyMarketAcctsNumber"]);
        $stmt->bindParam(':money_market_bank1', $mem_assets["moneyMarketBank1"]);
        $stmt->bindParam(':money_market_bank2', $mem_assets["moneyMarketBank2"]);
        $stmt->bindParam(':money_market_bank3', $mem_assets["moneyMarketBank3"]);
        $stmt->bindParam(':money_market_bank4', $mem_assets["moneyMarketBank4"]);
        $stmt->bindParam(':money_market_bank5', $mem_assets["moneyMarketBank5"]);
        $stmt->bindParam(':cert_deposit', $mem_assets["certDeposit"]);
        $stmt->bindParam(':cert_deposit_accts_number', $mem_assets["certDepositAcctsNumber"]);
        $stmt->bindParam(':cert_deposit_bank1', $mem_assets["certDepositBank1"]);
        $stmt->bindParam(':cert_deposit_bank2', $mem_assets["certDepositBank2"]);
        $stmt->bindParam(':cert_deposit_bank3', $mem_assets["certDepositBank3"]);
        $stmt->bindParam(':cert_deposit_bank4', $mem_assets["certDepositBank4"]);
        $stmt->bindParam(':cert_deposit_bank5', $mem_assets["certDepositBank5"]);
        $stmt->bindParam(':safe_deposit', $mem_assets["safeDeposit"]);
        $stmt->bindParam(':safe_deposit_value', $mem_assets["safeDepositValue"]);
        $stmt->bindParam(':stocks_bonds', $mem_assets["stocksBonds"]);
        $stmt->bindParam(':stocks_bonds_value', $mem_assets["stocksBondsValue"]);
        $stmt->bindParam(':pension_annuity', $mem_assets["pensionAnnuity"]);
        $stmt->bindParam(':real_estate_own', $mem_assets["realEstateOwn"]);
        $stmt->bindParam(':real_estate_value', $mem_assets["realEstateValue"]);
        $stmt->bindParam(':rental_property_own', $mem_assets["rentalPropertyOwn"]);
        $stmt->bindParam(':rental_property_value', $mem_assets["rentalPropertyValue"]);
        $stmt->bindParam(':sell_property', $mem_assets["sellProperty"]);
        $stmt->bindParam(':sell_property_address', $mem_assets["sellPropertyAddress"]);
        $stmt->bindParam(':sell_property_value', $mem_assets["sellPropertyValue"]);
        $stmt->bindParam(':sell_property_mortgage', $mem_assets["sellPropertyMortgage"]);
        $stmt->bindParam(':sell_property_expenses', $mem_assets["sellPropertyExpenses"]);
        $stmt->bindParam(':contract_property', $mem_assets["contractProperty"]);
        $stmt->bindParam(':contract_property_address', $mem_assets["contractPropertyAddress"]);
        $stmt->bindParam(':contract_property_value', $mem_assets["contractPropertyValue"]);
        $stmt->bindParam(':contract_property_mortgage', $mem_assets["contractPropertyMortgage"]);
        $stmt->bindParam(':contract_property_expenses', $mem_assets["contractPropertyExpenses"]);
        $stmt->bindParam(':life_insurance', $mem_assets["lifeInsurance"]);
        $stmt->bindParam(':personal_property', $mem_assets["personalProperty"]);
        $stmt->bindParam(':other_assets', $mem_assets["otherAssets"]);
        $stmt->bindParam(':other_assets_source', $mem_assets["otherAssetsSource"]);
        $stmt->bindParam(':other_assets_value', $mem_assets["otherAssetsValue"]);
        $stmt->bindParam(':disposed_assets', $mem_assets["disposedAssets"]);
        $stmt->bindParam(':cash_available', $mem_assets["cashAvailable"]);
        $stmt->bindParam(':cash_available_value', $mem_assets["cashAvailableValue"]);
        $stmt->bindParam(':total_assets', $mem_assets["totalAssets"]);
        $stmt->bindParam(':total_assets_value', $mem_assets["totalAssetsValue"]);        
        $stmt->execute();
    } catch (\Exception $e) {
        throw $e;
    }
}

/* =========================== */
/* MemberStudent functions     */
/* =========================== */
function getMemberStudent($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberStudent WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_student = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_student;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getMemberStudentFin($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberStudentFin WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_student = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_student;
    } catch (\Exception $e) {
        throw $e;
    }
}

function insertMemberStudent($userId,$mem_student) {
    global $db;
 
    echo "<p>In insertMemberStudent</p>\n";
    echo "<pre>". var_dump($mem_student) . "</pre>\n";
    try {
        $query = "REPLACE INTO MemberStudent 
                         (user_id,  student_household,  months_not_enrolled,  student_part_time,  student_title_IV,  student_foster,  student_job_training,  student_single_parent)
                  VALUES (:user_id, :student_household, :months_not_enrolled, :student_part_time, :student_title_IV, :student_foster, :student_job_training, :student_single_parent)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':student_household', $mem_student["studentHousehold"]);
        $stmt->bindParam(':months_not_enrolled', $mem_student["monthsNotEnrolled"]);
        $stmt->bindParam(':student_part_time', $mem_student["studentPartTime"]);
        $stmt->bindParam(':student_title_IV', $mem_student["studentTitleIV"]);
        $stmt->bindParam(':student_foster', $mem_student["studentFosterCare"]);
        $stmt->bindParam(':student_job_training', $mem_student["studentJobTraining"]);
        $stmt->bindParam(':student_single_parent', $mem_student["studentSingleParent"]);
        $stmt->execute();
    } catch (\Exception $e) {
        throw $e;
    }
}

function insertMemberStudentFin($userId,$mem_student) {
    global $db;
 
    echo "<p>In insertMemberStudentFin</p>\n";
    echo "<pre>". var_dump($mem_student) . "</pre>\n";
    try {
        $query = "REPLACE INTO MemberStudentFin 
                         (user_id,  institution_name,  institution_addr,  institution_city,  institution_state,  institution_zip,  institution_fax,  institution_email,
                                    student_name,  student_addr,  student_city,  student_state,  student_zip,  student_fax,  student_email)
                  VALUES (:user_id, :institution_name, :institution_addr, :institution_city, :institution_state, :institution_zip, :institution_fax, :institution_email,
                                    :student_name, :student_addr, :student_city, :student_state, :student_zip, :student_fax, :student_email)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':institution_name', $mem_student["institutionName"]);
        $stmt->bindParam(':institution_addr', $mem_student["institutionAddr"]);
        $stmt->bindParam(':institution_city', $mem_student["institutionCity"]);
        $stmt->bindParam(':institution_state', $mem_student["institutionState"]);
        $stmt->bindParam(':institution_zip', $mem_student["institutionZip"]);
        $stmt->bindParam(':institution_fax', $mem_student["institutionFax"]);
        $stmt->bindParam(':institution_email', $mem_student["institutionEmail"]);
        $stmt->bindParam(':student_name', $mem_student["studentName"]);
        $stmt->bindParam(':student_addr', $mem_student["studentAddr"]);
        $stmt->bindParam(':student_city', $mem_student["studentCity"]);
        $stmt->bindParam(':student_state', $mem_student["studentState"]);
        $stmt->bindParam(':student_zip', $mem_student["studentZip"]);
        $stmt->bindParam(':student_fax', $mem_student["studentFax"]);
        $stmt->bindParam(':student_email', $mem_student["studentEmail"]);
        $stmt->execute();
    } catch (\Exception $e) {
        throw $e;
    }
}

/* =========================== */
/* MemberEmployment functions  */
/* =========================== */
function getMemberEmployemnt($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberEmployer WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_employer = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_employer;
    } catch (\Exception $e) {
        throw $e;
    }
}

function addMemberEmployment($userId,$employer) {
    global $db;
 
    echo "<p>In AddMemberEmployment</p>\n";
    echo "<pre>". var_dump($employer) . "</pre>\n";
    try {
        $query = "SELECT * FROM MemberEmployer WHERE user_id = :user_id AND employer_name = :employer_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':employer_name', $employer);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberEmployer 
                             (user_id, employer_name)
                      VALUES (:user_id, :employer_name)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':employer_name', $employer);
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }

}

function updateMemberEmployment($userId,$mem_employment) {
    global $db;
 
    echo "<p>In insertMemberEmployment</p>\n";
    echo "<pre>". var_dump($mem_employment) . "</pre>\n";
    
    foreach (range(1, 5) as $i) { 
        
        $emp_name  = "employer" . $i . "Name";
        $emp_addr  = "employer" . $i . "Addr";
        $emp_city  = "employer" . $i . "City";
        $emp_state = "employer" . $i . "State";
        $emp_zip   = "employer" . $i . "Zip";
        $emp_email = "employer" . $i . "Email";
        $emp_fax   = "employer" . $i . "Fax";

        if ($mem_employment[$emp_name]) {
            try {
                $query = "UPDATE MemberEmployer SET employer_address = :employer_address, employer_city = :employer_city, employer_state = :employer_state, employer_zip = :employer_zip, employer_email = :employer_email, employer_fax = :employer_fax WHERE user_id = :user_id AND  employer_name = :employer_name";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':employer_address', $mem_employment[$emp_addr]);
                $stmt->bindParam(':employer_city', $mem_employment[$emp_city]);
                $stmt->bindParam(':employer_state', $mem_employment[$emp_state]);
                $stmt->bindParam(':employer_zip', $mem_employment[$emp_zip]);
                $stmt->bindParam(':employer_email', $mem_employment[$emp_email]);
                $stmt->bindParam(':employer_fax', $mem_employment[$emp_fax]);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':employer_name', $mem_employment[$emp_name]);
                $stmt->execute();
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}

/* ============================= */
/* MemberChildSupport functions  */
/* ============================= */
function getAllMemberChildSupport($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberChildSupport WHERE head_house = :head_house";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':head_house', $userId);
        $stmt->execute();
        $member_support = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_support;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getMemberChildSupport($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberChildSupport WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_support = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_support;
    } catch (\Exception $e) {
        throw $e;
    }
}

function addMemberChildSupport($userId,$child_support) {
    global $db;
 
    try {
        $query = "SELECT * FROM MemberChildSupport WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberChildSupport 
                             (user_id, head_house, both_parents, receive_child_support, receiving_future_support, receiving_future_explain, court_ordered_support, court_ordered_not_received, court_ordered_frequency, court_ordered_amount, non_court_support, non_court_frequency, non_court_amount)
                      VALUES (:user_id, :head_house, :both_parents, :receive_child_support, :receiving_future_support, :receiving_future_explain, :court_ordered_support, :court_ordered_not_received, :court_ordered_frequency, :court_ordered_amount, :non_court_support, :non_court_frequency, :non_court_amount)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':head_house',                 $child_support['head_house']);
            $stmt->bindParam(':both_parents',               $child_support['both_parents']);
            $stmt->bindParam(':receive_child_support',      $child_support['receive_child_support']);
            $stmt->bindParam(':receiving_future_support',   $child_support['receiving_future_support']);
            $stmt->bindParam(':receiving_future_explain',   $child_support['receiving_future_explain']);
            $stmt->bindParam(':court_ordered_support',      $child_support['court_ordered_support']);
            $stmt->bindParam(':court_ordered_not_received', $child_support['court_ordered_not_received']);
            $stmt->bindParam(':court_ordered_frequency',    $child_support['court_ordered_frequency']);
            $stmt->bindParam(':court_ordered_amount',       $child_support['court_ordered_amount']);
            $stmt->bindParam(':non_court_support',          $child_support['non_court_support']);
            $stmt->bindParam(':non_court_frequency',        $child_support['non_court_frequency']);
            $stmt->bindParam(':non_court_amount',           $child_support['non_court_amount']);
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }
}

function updateMemberChildSupport($userId,$child_support) {
    global $db;
 
    echo "<p>In updateMemberChildSupport</p>\n";
    echo "<pre>". var_dump($child_support) . "</pre>\n";
    
    foreach (range(1, 6) as $i) { 
        
        $cs_user              = "cs_userid" . $i;
        
        if ($child_support[$cs_user]) {
            $bothParents          = "bothParents" . $i;
            $childCourtOrdered    = "childCourtOrdered" . $i;
            $childPayments        = "childPayments" . $i;
            $childPaymentsAmt     = "childPaymentsAmt" . $i;
            $childNonCourtOrdered = "childNonCourtOrdered" . $i;
            $childNonPayments     = "childNonPayments" . $i;
            $childNonPaymentsAmt  = "childNonPaymentsAmt" . $i;
            
            try {
                $query = "SELECT * FROM MemberChildSupport WHERE user_id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $child_support[$cs_user]);
                $stmt->execute();
                if ($stmt->rowCount() <= 0) {
                    $query = "INSERT INTO MemberChildSupport 
                                     (user_id, head_house, both_parents_reside, court_ordered_support, court_ordered_frequency, court_ordered_amount, non_court_support, non_court_frequency, non_court_amount)
                              VALUES (:user_id, :head_house, :both_parents_reside, :court_ordered_support, :court_ordered_frequency, :court_ordered_amount, :non_court_support, :non_court_frequency, :non_court_amount)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id',                    $child_support[$cs_user]);
                    $stmt->bindParam(':head_house',                 $userId);
                    $stmt->bindParam(':both_parents_reside',        $child_support[$bothParents]);
                    $stmt->bindParam(':court_ordered_support',      $child_support[$childCourtOrdered]);
                    $stmt->bindParam(':court_ordered_frequency',    $child_support[$childPayments]);
                    $stmt->bindParam(':court_ordered_amount',       $child_support[$childPaymentsAmt]);
                    $stmt->bindParam(':non_court_support',          $child_support[$childNonCourtOrdered]);
                    $stmt->bindParam(':non_court_frequency',        $child_support[$childNonPayments]);
                    $stmt->bindParam(':non_court_amount',           $child_support[$childNonPaymentsAmt]);
                    $stmt->execute();
                } else {
                    $query = "UPDATE MemberChildSupport set head_house= :head_house, both_parents_reside= :both_parents_reside, court_ordered_support= :court_ordered_support, court_ordered_frequency= :court_ordered_frequency, court_ordered_amount= :court_ordered_amount, non_court_support= :non_court_support, non_court_frequency= :non_court_frequency, non_court_amount= :non_court_amount WHERE user_id= :user_id";              
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id',                    $child_support[$cs_user]);
                    $stmt->bindParam(':head_house',                 $userId);
                    $stmt->bindParam(':both_parents_reside',        $child_support[$bothParents]);
                    $stmt->bindParam(':court_ordered_support',      $child_support[$childCourtOrdered]);
                    $stmt->bindParam(':court_ordered_frequency',    $child_support[$childPayments]);
                    $stmt->bindParam(':court_ordered_amount',       $child_support[$childPaymentsAmt]);
                    $stmt->bindParam(':non_court_support',          $child_support[$childNonCourtOrdered]);
                    $stmt->bindParam(':non_court_frequency',        $child_support[$childNonPayments]);
                    $stmt->bindParam(':non_court_amount',           $child_support[$childNonPaymentsAmt]);
                    $stmt->execute();
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}

/* ============================= */
/* MemberAlimony functions       */
/* ============================= */
function getMemberAlimony($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberAlimony WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_alimony = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_alimony;
    } catch (\Exception $e) {
        throw $e;
    }
}

function addMemberAlimony($userId,$child_support) {
    global $db;
 
    try {
        $query = "SELECT * FROM MemberAlimony WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberAlimony 
                             (user_id, court_ordered_alimony, court_ordered_frequency, court_ordered_amount, non_court_alimony, non_court_frequency, non_court_amount, any_form_alimony)
                      VALUES (:user_id, :court_ordered_alimony, :court_ordered_frequency, :court_ordered_amount, :non_court_alimony, :non_court_frequency, :non_court_amount, :any_form_alimonyMemberAlimony)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':court_ordered_alimony',   $alimony['court_ordered_alimony']);
            $stmt->bindParam(':court_ordered_frequency', $alimony['court_ordered_frequency']);
            $stmt->bindParam(':court_ordered_amount',    $alimony['court_ordered_amount']);
            $stmt->bindParam(':non_court_alimony',       $alimony['non_court_alimony']);
            $stmt->bindParam(':non_court_frequency',     $alimony['non_court_frequency']);
            $stmt->bindParam(':non_court_amount',        $alimony['non_court_amount']);
            $stmt->bindParam(':any_form_alimony',        $alimony['any_form_alimony']);
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }
}

function updateMemberAlimony($userId,$alimony) {
    global $db;

    $court_ordered = 'no';
    $noncourt_ordered = 'no';
    $neither_ordered = '';
    
    if (($i = array_search("court", $alimony["alimonyCourtOrdered"])) !== FALSE) {
        $court_ordered = 'yes';
    }
    if (($i = array_search("noncourt", $alimony["alimonyCourtOrdered"])) !== FALSE) {
        $noncourt_ordered = 'yes';
    }
    if (($i = array_search("neither", $alimony["alimonyCourtOrdered"])) !== FALSE) {
        $neither_ordered = 'yes';
    }
        
    try {
        $query = "SELECT * FROM MemberAlimony WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberAlimony 
                             (user_id, receiving_child_support, receiving_future_support, receiving_future_explain, court_ordered_alimony, court_ordered_frequency, court_ordered_amount, non_court_alimony, non_court_frequency, non_court_amount, any_form_alimony)
                      VALUES (:user_id, :receiving_child_support, :receiving_future_support, :receiving_future_explain, :court_ordered_alimony, :court_ordered_frequency, :court_ordered_amount, :non_court_alimony, :non_court_frequency, :non_court_amount, :any_form_alimony)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id',                    $userId);
            $stmt->bindParam(':receiving_child_support',    $alimony["receiveSupport"]);
            $stmt->bindParam(':receiving_future_support',   $alimony["childAbsentReason"]);
            $stmt->bindParam(':receiving_future_explain',   $alimony["childOtherReason"]);
            $stmt->bindParam(':court_ordered_alimony',      $court_ordered);
            $stmt->bindParam(':court_ordered_frequency',    $alimony["alimonyPayments"]);
            $stmt->bindParam(':court_ordered_amount',       $alimony["alimonyPaymentsAmt"]);
            $stmt->bindParam(':non_court_alimony',          $court_ordered);
            $stmt->bindParam(':non_court_frequency',        $alimony["alimonyNonPayments"]);
            $stmt->bindParam(':non_court_amount',           $alimony["alimonyNonPaymentsAmt"]);
            $stmt->bindParam(':any_form_alimony',           $neither_ordered);
            $stmt->execute();
        } else {
            $query = "UPDATE MemberAlimony set receiving_child_support= :receiving_child_support, receiving_future_support= :receiving_future_support, receiving_future_explain= :receiving_future_explain, court_ordered_alimony= :court_ordered_alimony, court_ordered_frequency= :court_ordered_frequency, court_ordered_amount= :court_ordered_amount, non_court_alimony= :non_court_alimony, non_court_frequency= :non_court_frequency, non_court_amount= :non_court_amount, any_form_alimony= :any_form_alimony WHERE user_id= :user_id";              
            $stmt = $db->prepare($query);
            $stmt->bindParam(':receiving_child_support',    $alimony["receiveSupport"]);
            $stmt->bindParam(':receiving_future_support',   $alimony["childAbsentReason"]);
            $stmt->bindParam(':receiving_future_explain',   $alimony["childOtherReason"]);
            $stmt->bindParam(':court_ordered_alimony',      $court_ordered);
            $stmt->bindParam(':court_ordered_frequency',    $alimony["alimonyPayments"]);
            $stmt->bindParam(':court_ordered_amount',       $alimony["alimonyPaymentsAmt"]);
            $stmt->bindParam(':non_court_alimony',          $court_ordered);
            $stmt->bindParam(':non_court_frequency',        $alimony["alimonyNonPayments"]);
            $stmt->bindParam(':non_court_amount',           $alimony["alimonyNonPaymentsAmt"]);
            $stmt->bindParam(':any_form_alimony',           $neither_ordered);
            $stmt->bindParam(':user_id',                    $userId);
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }
}

/* ============================== */
/* MemberSelfEmployment functions */
/* ============================== */
function getMemberSelfEmployment($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberSelfEmployment WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $self_employment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $self_employment;
    } catch (\Exception $e) {
        throw $e;
    }
}

function updateMemberSelfEmployment($userId,$self_employment) {
    global $db;

    $self_employment["independantStartDate"] = date("Y-m-d", strtotime($self_employment["independantStartDate"]));
    $self_employment["businessStartDate"] = date("Y-m-d", strtotime($self_employment["businessStartDate"]));
    $self_employment["businessFedIncStart"] = date("Y-m-d", strtotime($self_employment["businessFedIncStart"]));
    $self_employment["businessFedIncEnd"] = date("Y-m-d", strtotime($self_employment["businessFedIncEnd"]));
    
    echo "<p>In updateMemberSelfEmployment</p>\n";
    echo "<pre>". var_dump($self_employment) . "</pre>\n";
    
    $self_employment["businessFedSched"] = implode(",", $self_employment["businessFedSched"]);

    try {
        $query = "SELECT * FROM MemberSelfEmployment WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberSelfEmployment 
                             (user_id, self_employed, independant_contractor, independant_start, independant_description, business_own, business_start, business_type, business_name, business_addr, business_email, business_phone, business_fed_tax, business_fed_taxyear, business_fed_sched, business_fed_less_year, business_fed_inc_start, business_fed_inc_end, business_fed_inc_amt, business_fed_proj_amt)
                      VALUES (:user_id, :self_employed, :independant_contractor, :independant_start, :independant_description, :business_own, :business_start, :business_type, :business_name, :business_addr, :business_email, :business_phone, :business_fed_tax, :business_fed_taxyear, :business_fed_sched, :business_fed_less_year, :business_fed_inc_start, :business_fed_inc_end, :business_fed_inc_amt, :business_fed_proj_amt)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id',                  $userId);
            $stmt->bindParam(':self_employed',            $self_employment["selfEmployed"]);
            $stmt->bindParam(':independant_contractor',   $self_employment["independantContractor"]);
            $stmt->bindParam(':independant_start',        $self_employment["independantStartDate"]);
            $stmt->bindParam(':independant_description',  $self_employment["independantDesc"]);
            $stmt->bindParam(':business_own',             $self_employment["ownBusiness"]);
            $stmt->bindParam(':business_start',           $self_employment["businessStartDate"]);
            $stmt->bindParam(':business_type',            $self_employment["businessType"]);
            $stmt->bindParam(':business_name',            $self_employment["businessName"]);
            $stmt->bindParam(':business_addr',            $self_employment["businessAddr"]);
            $stmt->bindParam(':business_email',           $self_employment["businessEmail"]);
            $stmt->bindParam(':business_phone',           $self_employment["businessPhone"]);
            $stmt->bindParam(':business_fed_tax',         $self_employment["businessFedTax"]);
            $stmt->bindParam(':business_fed_taxyear',     $self_employment["businessFedTaxYear"]);
            $stmt->bindParam(':business_fed_sched',       $self_employment["businessFedSched"]);
            $stmt->bindParam(':business_fed_less_year',   $self_employment["businessFedLessYear"]);
            $stmt->bindParam(':business_fed_inc_start',   $self_employment["businessFedIncStart"]);
            $stmt->bindParam(':business_fed_inc_end',     $self_employment["businessFedIncEnd"]);
            $stmt->bindParam(':business_fed_inc_amt',     $self_employment["businessFedIncAmount"]);
            $stmt->bindParam(':business_fed_proj_amt',    $self_employment["businessFedProjAmount"]);                      
            $stmt->execute();
        } else {
            $query = "UPDATE MemberSelfEmployment set self_employed= :self_employed, independant_contractor= :independant_contractor, independant_start= :independant_start, independant_description= :independant_description, business_own= :business_own, business_start= :business_start, business_type= :business_type, business_name= :business_name, business_addr= :business_addr, business_email= :business_email, business_phone= :business_phone, business_fed_tax= :business_fed_tax, business_fed_taxyear= :business_fed_taxyear, business_fed_sched= :business_fed_sched, business_fed_less_year= :business_fed_less_year, business_fed_inc_start= :business_fed_inc_start, business_fed_inc_end= :business_fed_inc_end, business_fed_inc_amt= :business_fed_inc_amt, business_fed_proj_amt= :business_fed_proj_amt WHERE user_id= :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':self_employed',            $self_employment["selfEmployed"]);
            $stmt->bindParam(':independant_contractor',   $self_employment["independantContractor"]);
            $stmt->bindParam(':independant_start',        $self_employment["independantStartDate"]);
            $stmt->bindParam(':independant_description',  $self_employment["independantDesc"]);
            $stmt->bindParam(':business_own',             $self_employment["ownBusiness"]);
            $stmt->bindParam(':business_start',           $self_employment["businessStartDate"]);
            $stmt->bindParam(':business_type',            $self_employment["businessType"]);
            $stmt->bindParam(':business_name',            $self_employment["businessName"]);
            $stmt->bindParam(':business_addr',            $self_employment["businessAddr"]);
            $stmt->bindParam(':business_email',           $self_employment["businessEmail"]);
            $stmt->bindParam(':business_phone',           $self_employment["businessPhone"]);
            $stmt->bindParam(':business_fed_tax',         $self_employment["businessFedTax"]);
            $stmt->bindParam(':business_fed_taxyear',     $self_employment["businessFedTaxYear"]);
            $stmt->bindParam(':business_fed_sched',       $self_employment["businessFedSched"]);
            $stmt->bindParam(':business_fed_less_year',   $self_employment["businessFedLessYear"]);
            $stmt->bindParam(':business_fed_inc_start',   $self_employment["businessFedIncStart"]);
            $stmt->bindParam(':business_fed_inc_end',     $self_employment["businessFedIncEnd"]);
            $stmt->bindParam(':business_fed_inc_amt',     $self_employment["businessFedIncAmount"]);
            $stmt->bindParam(':business_fed_proj_amt',    $self_employment["businessFedProjAmount"]);                      
            $stmt->bindParam(':user_id',                    $userId);
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }
}

/* =========================== */
/* MemberPension functions  */
/* =========================== */
function getMemberPension($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberPension WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_pension = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_pension;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getNextMemberPension($userId,$memberNum) {
    global $db;
   
    try {
        $query = "SELECT * FROM MemberPension WHERE user_id = :user_id LIMIT $memberNum,1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_pension = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_pension;
    } catch (\Exception $e) {
        throw $e;
    }
}

function addMemberPension($userId,$pension_entity) {
    global $db;
 
    echo "<p>In AddMemberEmployment</p>\n";
    echo "<pre>". var_dump($employer) . "</pre>\n";
    try {
        $query = "SELECT * FROM MemberPension WHERE user_id = :user_id AND pension_name = :pension_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':pension_name', $pension_entity);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberPension 
                             (user_id, pension_name)
                      VALUES (:user_id, :pension_name)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':pension_name', $pension_entity);
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }

}

function updateMemberPension($userId,$mem_pension) {
    global $db;
 
    echo "<p>In updateMemberPension</p>\n";
    echo "<pre>". var_dump($mem_pension) . "</pre>\n";
    
    foreach (range(1, 3) as $i) { 
        
        $pen_name  = "pension" . $i . "Name";
        $pen_addr  = "pension" . $i . "Addr";
        $pen_city  = "pension" . $i . "City";
        $pen_state = "pension" . $i . "State";
        $pen_zip   = "pension" . $i . "Zip";
        $pen_email = "pension" . $i . "Email";
        $pen_fax   = "pension" . $i . "Fax";

        if ($mem_pension[$pen_name]) {
            try {
                $query = "REPLACE INTO MemberPension
                             (user_id, pension_name, pension_address, pension_city, pension_state, pension_zip, pension_email, pension_fax)
                             VALUES(:user_id, :pension_name, :pension_address, :pension_city, :pension_state, :pension_zip, :pension_email, :pension_fax)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':pension_name', $mem_pension[$pen_name]);
                $stmt->bindParam(':pension_address', $mem_pension[$pen_addr]);
                $stmt->bindParam(':pension_city', $mem_pension[$pen_city]);
                $stmt->bindParam(':pension_state', $mem_pension[$pen_state]);
                $stmt->bindParam(':pension_zip', $mem_pension[$pen_zip]);
                $stmt->bindParam(':pension_email', $mem_pension[$pen_email]);
                $stmt->bindParam(':pension_fax', $mem_pension[$pen_fax]);
                $stmt->execute();
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}

/* =========================== */
/* MemberAnnuity functions  */
/* =========================== */
function getMemberAnnuity($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberAnnuity WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_annuity = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_annuity;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getNextMemberAnnuity($userId,$memberNum) {
    global $db;
   
    try {
        $query = "SELECT * FROM MemberAnnuity WHERE user_id = :user_id LIMIT $memberNum,1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_annuity = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_annuity;
    } catch (\Exception $e) {
        throw $e;
    }
}

function addMemberAnnuity($userId,$annuity_entity) {
    global $db;
 
    echo "<p>In addMemberAnnuity</p>\n";
    echo "<pre>". var_dump($annuity_entity) . "</pre>\n";
    
    try {
        $query = "SELECT * FROM MemberAnnuity WHERE user_id = :user_id AND annuity_name = :annuity_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':annuity_name', $annuity_entity);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberAnnuity 
                             (user_id, annuity_name)
                      VALUES (:user_id, :annuity_name)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':annuity_name', $annuity_entity);
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }

}

function updateMemberAnnuity($userId,$mem_annuity) {
    global $db;
 
    echo "<p>In updateMemberAnnuity</p>\n";
    echo "<pre>". var_dump($mem_annuity) . "</pre>\n";
    
    foreach (range(1, 3) as $i) { 
        
        $ann_name  = "annuity" . $i . "Name";
        $ann_addr  = "annuity" . $i . "Addr";
        $ann_city  = "annuity" . $i . "City";
        $ann_state = "annuity" . $i . "State";
        $ann_zip   = "annuity" . $i . "Zip";
        $ann_email = "annuity" . $i . "Email";
        $ann_fax   = "annuity" . $i . "Fax";

        if ($mem_annuity[$ann_name]) {
            try {
                $query = "SELECT * FROM MemberAnnuity WHERE user_id = :user_id AND annuity_name = :annuity_name";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':annuity_name', $mem_annuity[$ann_name]);
                $stmt->execute();
                if ($stmt->rowCount() <= 0) {
                    $query = "INSERT INTO MemberAnnuity
                                 (user_id, annuity_name, annuity_address, annuity_city, annuity_state, annuity_zip, annuity_email, annuity_fax)
                                 VALUES(:user_id, :annuity_name, :annuity_address, :annuity_city, :annuity_state, :annuity_zip, :annuity_email, :annuity_fax)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':annuity_name', $mem_annuity[$ann_name]);
                    $stmt->bindParam(':annuity_address', $mem_annuity[$ann_addr]);
                    $stmt->bindParam(':annuity_city', $mem_annuity[$ann_city]);
                    $stmt->bindParam(':annuity_state', $mem_annuity[$ann_state]);
                    $stmt->bindParam(':annuity_zip', $mem_annuity[$ann_zip]);
                    $stmt->bindParam(':annuity_email', $mem_annuity[$ann_email]);
                    $stmt->bindParam(':annuity_fax', $mem_annuity[$ann_fax]);
                    $stmt->execute();
                } else {
                    $query = "UPDATE MemberAnnuity annuity_address= :annuity_address, annuity_city= :annuity_city, annuity_state= :annuity_state, annuity_zip= :annuity_zip, annuity_email= :annuity_email, annuity_fax= :annuity_fax WHERE user_id= :user_id AND annuity_name= :annuity_name";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':annuity_address', $mem_annuity[$ann_addr]);
                    $stmt->bindParam(':annuity_city', $mem_annuity[$ann_city]);
                    $stmt->bindParam(':annuity_state', $mem_annuity[$ann_state]);
                    $stmt->bindParam(':annuity_zip', $mem_annuity[$ann_zip]);
                    $stmt->bindParam(':annuity_email', $mem_annuity[$ann_email]);
                    $stmt->bindParam(':annuity_fax', $mem_annuity[$ann_fax]);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':annuity_name', $mem_annuity[$ann_name]);
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}

/* =========================== */
/* MemberRetirement functions  */
/* =========================== */
function getMemberRetirement($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberRetirement WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_retire = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_retire;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getNextMemberRetirement($userId,$memberNum) {
    global $db;
   
    try {
        $query = "SELECT * FROM MemberRetirement WHERE user_id = :user_id LIMIT $memberNum,1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_retire = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_retire;
    } catch (\Exception $e) {
        throw $e;
    }
}

function addMemberRetirement($userId,$retire_entity) {
    global $db;
 
    echo "<p>In addMemberRetirement</p>\n";
    echo "<pre>". var_dump($retire_entity) . "</pre>\n";
    
    try {
        $query = "SELECT * FROM MemberRetirement WHERE user_id = :user_id AND retire_name = :retire_name";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':retire_name', $retire_entity);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberRetirement 
                             (user_id, retire_name)
                      VALUES (:user_id, :retire_name)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':retire_name', $retire_entity);
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }

}

function updateMemberRetirement($userId,$mem_retire) {
    global $db;
 
    echo "<p>In updateMemberRetirement</p>\n";
    echo "<pre>". var_dump($mem_retire) . "</pre>\n";
    
    foreach (range(1, 3) as $i) { 
        
        $ann_name  = "retire" . $i . "Name";
        $ann_addr  = "retire" . $i . "Addr";
        $ann_city  = "retire" . $i . "City";
        $ann_state = "retire" . $i . "State";
        $ann_zip   = "retire" . $i . "Zip";
        $ann_email = "retire" . $i . "Email";
        $ann_fax   = "retire" . $i . "Fax";

        if ($mem_retire[$ann_name]) {
            try {
                $query = "SELECT * FROM MemberRetirement WHERE user_id = :user_id AND retire_name = :retire_name";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':retire_name', $mem_retire[$ann_name]);
                $stmt->execute();
                if ($stmt->rowCount() <= 0) {
                    $query = "INSERT INTO MemberRetirement
                                 (user_id, retire_name, retire_address, retire_city, retire_state, retire_zip, retire_email, retire_fax)
                                 VALUES(:user_id, :retire_name, :retire_address, :retire_city, :retire_state, :retire_zip, :retire_email, :retire_fax)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':retire_name', $mem_retire[$ann_name]);
                    $stmt->bindParam(':retire_address', $mem_retire[$ann_addr]);
                    $stmt->bindParam(':retire_city', $mem_retire[$ann_city]);
                    $stmt->bindParam(':retire_state', $mem_retire[$ann_state]);
                    $stmt->bindParam(':retire_zip', $mem_retire[$ann_zip]);
                    $stmt->bindParam(':retire_email', $mem_retire[$ann_email]);
                    $stmt->bindParam(':retire_fax', $mem_retire[$ann_fax]);
                    $stmt->execute();
                } else {
                    $query = "UPDATE MemberRetirement retire_address= :retire_address, retire_city= :retire_city, retire_state= :retire_state, retire_zip= :retire_zip, retire_email= :retire_email, retire_fax= :retire_fax WHERE user_id= :user_id AND retire_name= :retire_name";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':retire_address', $mem_retire[$ann_addr]);
                    $stmt->bindParam(':retire_city', $mem_retire[$ann_city]);
                    $stmt->bindParam(':retire_state', $mem_retire[$ann_state]);
                    $stmt->bindParam(':retire_zip', $mem_retire[$ann_zip]);
                    $stmt->bindParam(':retire_email', $mem_retire[$ann_email]);
                    $stmt->bindParam(':retire_fax', $mem_retire[$ann_fax]);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':retire_name', $mem_retire[$ann_name]);
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}

/* ============================ */
/* MemberDivestiture functions  */
/* ============================ */
function getMemberDivestiture($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberDivestiture WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_retire = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_retire;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getNextMemberDivestiture($userId,$memberNum) {
    global $db;
   
    try {
        $query = "SELECT * FROM MemberDivestiture WHERE user_id = :user_id LIMIT $memberNum,1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_retire = $stmt->fetch(PDO::FETCH_ASSOC);
        return $member_retire;
    } catch (\Exception $e) {
        throw $e;
    }
}

function addMemberDivestiture($userId,$divest_entity) {
    global $db;
 
    echo "<p>In addMemberDivestiture</p>\n";
    echo "<pre>". var_dump($retire_entity) . "</pre>\n";
    
    try {
        $query = "SELECT * FROM MemberDivestiture WHERE user_id = :user_id AND divest_type = :divest_type";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':divest_type', $divest_entity);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberDivestiture 
                             (user_id, divest_type)
                      VALUES (:user_id, :divest_type)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':divest_type', $divest_entity);
            $stmt->execute();
        }
    } catch (\Exception $e) {
        throw $e;
    }

}

function updateMemberDivestiture($userId,$mem_divest) {
    global $db;
 
    echo "<p>In updateMemberDivestiture</p>\n";
    echo "<pre>". var_dump($mem_divest) . "</pre>\n";
    
    foreach (range(1, 3) as $i) { 
        
        $div_type  = "divest" . $i . "Type";
        $div_date  = "divest" . $i . "Date";
        $div_market  = "divest" . $i . "Market";
        $div_cost = "divest" . $i . "Cost";
        $div_amount   = "divest" . $i . "Amount";
        $div_reason = "divest" . $i . "Reason";

        if ($mem_divest[$div_type]) {
            try {
                $query = "SELECT * FROM MemberDivestiture WHERE user_id = :user_id AND divest_type = :divest_type";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':divest_type', $mem_divest[$div_type]);
                $stmt->execute();
                if ($stmt->rowCount() <= 0) {
                    $query = "INSERT INTO MemberDivestiture
                                 (user_id, divest_type, divest_date, divest_market, divest_cost, divest_amount, divest_reason)
                                 VALUES(:user_id, :divest_type, :divest_date, :divest_market, :divest_cost, :divest_amount, :divest_reason)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':divest_type', $mem_divest[$div_type]);
                    $stmt->bindParam(':divest_date', $mem_divest[$div_date]);
                    $stmt->bindParam(':divest_market', $mem_divest[$div_market]);
                    $stmt->bindParam(':divest_cost', $mem_divest[$div_cost]);
                    $stmt->bindParam(':divest_amount', $mem_divest[$div_amount]);
                    $stmt->bindParam(':divest_reason', $mem_divest[$div_reason]);
                    $stmt->execute();
                } else {
                    $query = "UPDATE MemberDivestiture divest_date= :divest_date, divest_market= :divest_market, divest_cost= :divest_cost, divest_amount= :divest_amount, divest_reason= :divest_reason WHERE user_id= :user_id AND divest_type= :divest_type";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':divest_date', $mem_divest[$div_date]);
                    $stmt->bindParam(':divest_market', $mem_divest[$div_market]);
                    $stmt->bindParam(':divest_cost', $mem_divest[$div_cost]);
                    $stmt->bindParam(':divest_amount', $mem_divest[$div_amount]);
                    $stmt->bindParam(':divest_reason', $mem_divest[$div_reason]);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->bindParam(':divest_type', $mem_divest[$div_type]);
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }
}

/* ============================ */
/* MemberUnder4k functions  */
/* ============================ */
function getMemberUnder4k($userId) {
    global $db;
    
    try {
        $query = "SELECT * FROM MemberUnder4k WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $member_under = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $member_under;
    } catch (\Exception $e) {
        throw $e;
    }
}

function updateMemberUnder4k($userId,$mem_under) {
    global $db;
 
    echo "<p>In updateMemberUnder4k</p>\n";
    echo "<pre>". var_dump($mem_under) . "</pre>\n";

    try {
        $query = "SELECT * FROM MemberUnder4k WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        if ($stmt->rowCount() <= 0) {
            $query = "INSERT INTO MemberUnder4k
                         (user_id, checkingaccount_value,checkingaccount_rate,checkingaccount_annual,savingsaccount_value,savingsaccount_rate,savingsaccount_annual,cashaccount_value,cashaccount_rate,cashaccount_annual,
                         moneymarket_value,moneymarket_rate,moneymarket_annual,stocksbonds_value,stocksbonds_rate,stocksbonds_annual,safedeposit_value,safedeposit_rate,safedeposit_annual,retire401k_value,retire401k_rate,retire401k_annual,
                         individualretire_value,individualretire_rate,individualretire_annual,trustfund_value,trustfund_rate,trustfund_annual,realestate_value,realestate_rate,realestate_annual,lifeinsurance_value,lifeinsurance_rate,lifeinsurance_annual,
                         pensionaccount_value,pensionaccount_rate,pensionaccount_annual,otherassets_value,otherassets_rate,otherassets_annual )
                      VALUES(:user_id, :checkingAccountValue, :checkingAccountRate, :checkingAccountAnnual, :savingsAccountValue, :savingsAccountRate, :savingsAccountAnnual, :cashAccountValue, :cashAccountRate, :cashAccountAnnual, 
                        :moneyMarketValue, :moneyMarketRate, :moneyMarketAnnual, :stocksBondsValue, :stocksBondsRate, :stocksBondsAnnual, :safeDepositValue, :safeDepositRate, :safeDepositAnnual, :retire401KValue, :retire401KRate, :retire401KAnnual, 
                        :individualRetireValue, :individualRetireRate, :individualRetireAnnual, :trustFundValue, :trustFundRate, :trustFundAnnual, :realEstateValue, :realEstateRate, :realEstateAnnual, 
                        :lifeInsuranceValue, :lifeInsuranceRate, :lifeInsuranceAnnual, :pensionAccountValue, :pensionAccountRate, :pensionAccountAnnual, :otherAssetsValue, :otherAssetsRate, :otherAssetsAnnual )";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':checkingAccountValue', $mem_under["checkingAccountValue"]);
            $stmt->bindParam(':checkingAccountRate', $mem_under["checkingAccountRate"]);
            $stmt->bindParam(':checkingAccountAnnual', $mem_under["checkingAccountAnnual"]);
            $stmt->bindParam(':savingsAccountValue', $mem_under["savingsAccountValue"]);
            $stmt->bindParam(':savingsAccountRate', $mem_under["savingsAccountRate"]);
            $stmt->bindParam(':savingsAccountAnnual', $mem_under["savingsAccountAnnual"]);
            $stmt->bindParam(':cashAccountValue', $mem_under["cashAccountValue"]);
            $stmt->bindParam(':cashAccountRate', $mem_under["cashAccountRate"]);
            $stmt->bindParam(':cashAccountAnnual', $mem_under["cashAccountAnnual"]);
            $stmt->bindParam(':moneyMarketValue', $mem_under["moneyMarketValue"]);
            $stmt->bindParam(':moneyMarketRate', $mem_under["moneyMarketRate"]);
            $stmt->bindParam(':moneyMarketAnnual', $mem_under["moneyMarketAnnual"]);
            $stmt->bindParam(':stocksBondsValue', $mem_under["stocksBondsValue"]);
            $stmt->bindParam(':stocksBondsRate', $mem_under["stocksBondsRate"]);
            $stmt->bindParam(':stocksBondsAnnual', $mem_under["stocksBondsAnnual"]);
            $stmt->bindParam(':safeDepositValue', $mem_under["safeDepositValue"]);
            $stmt->bindParam(':safeDepositRate', $mem_under["safeDepositRate"]);
            $stmt->bindParam(':safeDepositAnnual', $mem_under["safeDepositAnnual"]);
            $stmt->bindParam(':retire401KValue', $mem_under["retire401KValue"]);
            $stmt->bindParam(':retire401KRate', $mem_under["retire401KRate"]);
            $stmt->bindParam(':retire401KAnnual', $mem_under["retire401KAnnual"]);
            $stmt->bindParam(':individualRetireValue', $mem_under["individualRetireValue"]);
            $stmt->bindParam(':individualRetireRate', $mem_under["individualRetireRate"]);
            $stmt->bindParam(':individualRetireAnnual', $mem_under["individualRetireAnnual"]);
            $stmt->bindParam(':trustFundValue', $mem_under["trustFundValue"]);
            $stmt->bindParam(':trustFundRate', $mem_under["trustFundRate"]);
            $stmt->bindParam(':trustFundAnnual', $mem_under["trustFundAnnual"]);
            $stmt->bindParam(':realEstateValue', $mem_under["realEstateValue"]);
            $stmt->bindParam(':realEstateRate', $mem_under["realEstateRate"]);
            $stmt->bindParam(':realEstateAnnual', $mem_under["realEstateAnnual"]);
            $stmt->bindParam(':lifeInsuranceValue', $mem_under["lifeInsuranceValue"]);
            $stmt->bindParam(':lifeInsuranceRate', $mem_under["lifeInsuranceRate"]);
            $stmt->bindParam(':lifeInsuranceAnnual', $mem_under["lifeInsuranceAnnual"]);
            $stmt->bindParam(':pensionAccountValue', $mem_under["pensionAccountValue"]);
            $stmt->bindParam(':pensionAccountRate', $mem_under["pensionAccountRate"]);
            $stmt->bindParam(':pensionAccountAnnual', $mem_under["pensionAccountAnnual"]);
            $stmt->bindParam(':otherAssetsValue', $mem_under["otherAssetsValue"]);
            $stmt->bindParam(':otherAssetsRate', $mem_under["otherAssetsRate"]);
            $stmt->bindParam(':otherAssetsAnnual', $mem_under["otherAssetsAnnual"]);
            $stmt->execute();
        } else {
            $query = "UPDATE MemberUnder4k checkingaccount_value= :checkingAccountValue, checkingaccount_rate= :checkingAccountRate, checkingaccount_annual= :checkingAccountAnnual, 
                        savingsaccount_value= :savingsAccountValue, savingsaccount_rate= :savingsAccountRate, savingsaccount_annual= :savingsAccountAnnual, 
                        cashaccount_value= :cashAccountValue, cashaccount_rate= :cashAccountRate, cashaccount_annual= :cashAccountAnnual, 
                        moneymarket_value= :moneyMarketValue, moneymarket_rate= :moneyMarketRate, moneymarket_annual= :moneyMarketAnnual, 
                        stocksbonds_value= :stocksBondsValue, stocksbonds_rate= :stocksBondsRate, stocksbonds_annual= :stocksBondsAnnual, 
                        safedeposit_value= :safeDepositValue, safedeposit_rate= :safeDepositRate, safedeposit_annual= :safeDepositAnnual, 
                        retire401k_value= :retire401KValue, retire401k_rate= :retire401KRate, retire401k_annual= :retire401KAnnual, 
                        individualretire_value= :individualRetireValue, individualretire_rate= :individualRetireRate, individualretire_annual= :individualRetireAnnual, 
                        trustfund_value= :trustFundValue, trustfund_rate= :trustFundRate, trustfund_annual= :trustFundAnnual, 
                        realestate_value= :realEstateValue, realestate_rate= :realEstateRate, realestate_annual= :realEstateAnnual, 
                        lifeinsurance_value= :lifeInsuranceValue, lifeinsurance_rate= :lifeInsuranceRate, lifeinsurance_annual= :lifeInsuranceAnnual, 
                        pensionaccount_value= :pensionAccountValue, pensionaccount_rate= :pensionAccountRate, pensionaccount_annual= :pensionAccountAnnual, 
                        otherassets_value= :otherAssetsValue, otherassets_rate= :otherAssetsRate, otherassets_annual= :otherAssetsAnnual WHERE user_id= :user_id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':checkingAccountValue', $mem_under["checkingAccountValue"]);
            $stmt->bindParam(':checkingAccountRate', $mem_under["checkingAccountRate"]);
            $stmt->bindParam(':checkingAccountAnnual', $mem_under["checkingAccountAnnual"]);
            $stmt->bindParam(':savingsAccountValue', $mem_under["savingsAccountValue"]);
            $stmt->bindParam(':savingsAccountRate', $mem_under["savingsAccountRate"]);
            $stmt->bindParam(':savingsAccountAnnual', $mem_under["savingsAccountAnnual"]);
            $stmt->bindParam(':cashAccountValue', $mem_under["cashAccountValue"]);
            $stmt->bindParam(':cashAccountRate', $mem_under["cashAccountRate"]);
            $stmt->bindParam(':cashAccountAnnual', $mem_under["cashAccountAnnual"]);
            $stmt->bindParam(':moneyMarketValue', $mem_under["moneyMarketValue"]);
            $stmt->bindParam(':moneyMarketRate', $mem_under["moneyMarketRate"]);
            $stmt->bindParam(':moneyMarketAnnual', $mem_under["moneyMarketAnnual"]);
            $stmt->bindParam(':stocksBondsValue', $mem_under["stocksBondsValue"]);
            $stmt->bindParam(':stocksBondsRate', $mem_under["stocksBondsRate"]);
            $stmt->bindParam(':stocksBondsAnnual', $mem_under["stocksBondsAnnual"]);
            $stmt->bindParam(':safeDepositValue', $mem_under["safeDepositValue"]);
            $stmt->bindParam(':safeDepositRate', $mem_under["safeDepositRate"]);
            $stmt->bindParam(':safeDepositAnnual', $mem_under["safeDepositAnnual"]);
            $stmt->bindParam(':retire401KValue', $mem_under["retire401KValue"]);
            $stmt->bindParam(':retire401KRate', $mem_under["retire401KRate"]);
            $stmt->bindParam(':retire401KAnnual', $mem_under["retire401KAnnual"]);
            $stmt->bindParam(':individualRetireValue', $mem_under["individualRetireValue"]);
            $stmt->bindParam(':individualRetireRate', $mem_under["individualRetireRate"]);
            $stmt->bindParam(':individualRetireAnnual', $mem_under["individualRetireAnnual"]);
            $stmt->bindParam(':trustFundValue', $mem_under["trustFundValue"]);
            $stmt->bindParam(':trustFundRate', $mem_under["trustFundRate"]);
            $stmt->bindParam(':trustFundAnnual', $mem_under["trustFundAnnual"]);
            $stmt->bindParam(':realEstateValue', $mem_under["realEstateValue"]);
            $stmt->bindParam(':realEstateRate', $mem_under["realEstateRate"]);
            $stmt->bindParam(':realEstateAnnual', $mem_under["realEstateAnnual"]);
            $stmt->bindParam(':lifeInsuranceValue', $mem_under["lifeInsuranceValue"]);
            $stmt->bindParam(':lifeInsuranceRate', $mem_under["lifeInsuranceRate"]);
            $stmt->bindParam(':lifeInsuranceAnnual', $mem_under["lifeInsuranceAnnual"]);
            $stmt->bindParam(':pensionAccountValue', $mem_under["pensionAccountValue"]);
            $stmt->bindParam(':pensionAccountRate', $mem_under["pensionAccountRate"]);
            $stmt->bindParam(':pensionAccountAnnual', $mem_under["pensionAccountAnnual"]);
            $stmt->bindParam(':otherAssetsValue', $mem_under["otherAssetsValue"]);
            $stmt->bindParam(':otherAssetsRate', $mem_under["otherAssetsRate"]);
            $stmt->bindParam(':otherAssetsAnnual', $mem_under["otherAssetsAnnual"]);
            $stmt->bindParam(':user_id', $userId);
        }
    } catch (\Exception $e) {
        throw $e;
    }
}

/* ========================== */
/* Misc functions             */
/* ========================== */
function getInstitutionCampus($campus_state)
{
    global $db;
    
    try {
        $query = "select DapipId,OpeId,LocationName,CampusCity,CampusState from InstitutionCampus where CampusState = :campus_state order by LocationName";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':campus_state', $campus_state);
        $stmt->execute();
        $campus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $campus;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getInstitutionState()
{
    global $db;
    
    try {
        $query = "SELECT DISTINCT CampusState FROM InstitutionCampus ORDER BY CampusState";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $campus_state = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $campus_state;
    } catch (\Exception $e) {
        throw $e;
    }
}

function insertMemberSignature($userId,$sig_data) {
    global $db;
 
    try {
        $query = "REPLACE INTO MemberSignature (user_id,  sign_data) VALUES (:user_id, :sign_data)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':sign_data', $sig_data);
        $stmt->execute();
    } catch (\Exception $e) {
        throw $e;
    }
}

function getFormDesc($form_abbr)
{
    global $db;
    
    try {
        $query = "SELECT * FROM Forms WHERE form_abbr = :form_abbr";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':form_abbr', $form_abbr);
        $stmt->execute();
        $form = $stmt->fetch(PDO::FETCH_ASSOC);
        return $form;
    } catch (\Exception $e) {
        throw $e;
    }
}

function getFileList($user)
{
    $this_directory = '';
    
  	$objects = array();
	$objects['directories'] = array();
	$objects['files'] = array();
	$return_files = array();
    
    # get file listing from FoxFace upload directory accourding to PM or Res
    $this_dir_restrict = '';
    if ($user["role_id"] == '2') {
        $this_directory = "/var/www/html3/documents/" . $user["user_id"] . "/*";
    } else {
        $this_directory = "/var/www/html3/documents/" . $user["pm_id"] . "/*";
        $this_dir_restrict = $user["user_id"];
    }
    
    $items = scandir( dirname($this_directory) );
    
	foreach($items as $c => $item)
	{
		if( $item == ".." OR $item == ".") continue;
	
		// DIRECTORIES
		if( is_dir($item) ) 
		{
			$objects['directories'][] = $item; 
			continue;
		}
		
		// FILE DATE
		$file_time = date("U", filemtime(substr($this_directory, 0, -1) . $item));
		
		// FILES
		if( $item )
		{
			$objects['files'][$file_time . "-" . $item] = $item;
		}
	}
	
	krsort($objects['files']);
	
	foreach($objects['files'] as $t => $file)
	{
        if ($this_dir_restrict) {
            // Beggining of tile name must start with this tenant's userid.
            if ($this_dir_restrict <> substr($file, 0, 4)) {
                continue;
            }
        }	 
        
	    $file_dir = substr($this_directory, 15, -1) . $file;
	    $file = substr($file, 5);
	    $file = substr($file, 0, -4);
	    
	    $return_files[$file_dir] = $file;
	}
	
	return $return_files;
}


function getAge($date)
{
    $dob = new DateTime($date);
    $now = new DateTime();
    $difference = $now->diff($dob);
    $age = $difference->y;
    return  $age;
}

function checkValidEmail($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else {
        return false;
    }
}

function insertPasswordReset($email,$selector,$token,$expires) {
    global $db;
    
    try {
        $query = "INSERT INTO PasswordReset (email, selector, token, expires) VALUES (:email, :selector, :token, :expires)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':selector', $selector);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expires', $expires);
        $stmt->execute();
    } catch (\Exception $e) {
        throw $e;
    }
}

function getPasswordReset($selector) {
    global $db;
    
    try {
        $query = "SELECT * FROM PasswordReset WHERE selector = :selector AND  expires >= :time";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':selector', $selector);
        $stmt->bindParam(':time', time());
        $stmt->execute();
        $p_reset = $stmt->fetch(PDO::FETCH_ASSOC);  
        return $p_reset;
    } catch (\Exception $e) {
        throw $e;
    }
    
    return true;
}

function deletePasswordReset($email) {
    global $db;
    
    try {
        $query = 'DELETE FROM PasswordReset WHERE email=:email';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    } catch (\Exception $e) {
        return false;
    }
    
    return true;
}

function decodeJwt($prop = null) {
    \Firebase\JWT\JWT::$leeway = 1;
    $jwt = \Firebase\JWT\JWT::decode(
        request()->cookies->get('access_token'),
        getenv('SECRET_KEY'),
        ['HS256']
    );
    
    if ($prop === null) {
        return $jwt;
    }
    
    return $jwt->{$prop};
}

function isAuthenticated() {
    if (!request()->cookies->has('access_token')) {
        return false;
    }
    
    try {
        decodeJwt();
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

function requireAuth() {
    if(!isAuthenticated()) {
        $accessToken = new \Symfony\Component\HttpFoundation\Cookie("access_token", "Expired", time()-18000, '/', getenv('COOKIE_DOMAIN'));
        redirect('/login.php', ['cookies' => [$accessToken]]);
    }
}

function requireAdmin() {
    global $session;
    if(!isAuthenticated()) {
        $accessToken = new \Symfony\Component\HttpFoundation\Cookie("access_token", "Expired", time()-18000, '/', getenv('COOKIE_DOMAIN'));
        redirect('/login.php', ['cookies' => [$accessToken]]);
    }
    
    try {
        if (! decodeJwt('is_admin')) {
            $session->getFlashBag()->add('error', 'Not Authorized');
            redirect('/');
        }
    } catch (\Exception $e) {
        $accessToken = new \Symfony\Component\HttpFoundation\Cookie("access_token", "Expired", time()-18000, '/', getenv('COOKIE_DOMAIN'));
        redirect('/login.php', ['cookies' => [$accessToken]]);
    }
}

function requirePropManager() {
    global $session;
    if(!isAuthenticated()) {
        $accessToken = new \Symfony\Component\HttpFoundation\Cookie("access_token", "Expired", time()-18000, '/', getenv('COOKIE_DOMAIN'));
        redirect('/login.php', ['cookies' => [$accessToken]]);
    }
    
    try {
        if (! decodeJwt('is_admin')) {
            $session->getFlashBag()->add('error', 'Not Authorized');
            redirect('/');
        }
    } catch (\Exception $e) {
        $accessToken = new \Symfony\Component\HttpFoundation\Cookie("access_token", "Expired", time()-18000, '/', getenv('COOKIE_DOMAIN'));
        redirect('/login.php', ['cookies' => [$accessToken]]);
    }
}

function isAdmin() {
    if (!isAuthenticated()) {
        return false;
    }
    
    try {
        $isAdmin = decodeJwt('is_admin');
    } catch (\Exception $e) {
        return false;
    }
    
    return (boolean)$isAdmin;
}

function display_errors() {
    global $session;
    
    if (!$session->getFlashBag()->has('error')) {
        return;
    }
    
    $messages = $session->getFlashBag()->get('error');
    
    $response = '<div style="background-color: red; color: white;border: 0">';
    foreach ($messages as $message) {
        $response .= "{$message}<br />";
    }
    $response .= '</div>';
    
    return $response;
}

function display_success() {
    global $session;

    if(!$session->getFlashBag()->has('success')) {
        return;
    }

    $messages = $session->getFlashBag()->get('success');

    $response = '<div style="background-color: green; color: white;border: 0">';
    foreach($messages as $message ) {
        $response .= "{$message}<br>";
    }
    $response .= '</div>';

    return $response;
}

function display_notice() {
    global $session;

    if(!$session->getFlashBag()->has('notice')) {
        return;
    }

    $messages = $session->getFlashBag()->get('notice');

    $response = '<div style="background-color: orange; color: white;border: 0">';
    foreach($messages as $message ) {
        $response .= "{$message}<br>";
    }
    $response .= '</div>';

    return $response;
}

function email_admin($message) {
    global $session;

    $to = "cris.inc@comcast.net";
    $subject = "New user registration";
    $headers = "From: registration@criminalinfo.net" . "\r\n" .
    "CC: cris.tech@comcast.net";
    
    mail($to,$subject,$message,$headers);
}

function lookup_num_adjective($datatype){
    $array = array(
        'First'   => '1',
        'Second'  => '2',
        'Third'   => '3',
        'Fourth'  => '4', 
        'Fifth'   => '5',
        'Sixth'   => '6',
        'Seventh' => '7',
        'Eighth'  => '8',
        'Nineth'  => '9',
        'Tenth'   => '10'
        );

    $key = array_search($datatype, $array);

    if ($key !== false) {
        return $key;
    } else { 
        return $datatype;
    }
}

function lookup_num_name($datatype){
    $array = array(
        'One'   => '1',
        'Two'   => '2',
        'Three' => '3',
        'Four'  => '4', 
        'Five'  => '5',
        'Six'   => '6',
        'Seven' => '7',
        'Eight' => '8',
        'Nine'  => '9',
        'Ten'   => '10'
        );

    $key = array_search($datatype, $array);

    if ($key !== false) {
        return $key;
    } else { 
        return $datatype;
    }
}

?>