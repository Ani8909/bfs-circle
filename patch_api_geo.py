import os

api_path = r'c:\Users\pc\Downloads\client mgmt2\api.php'
with open(api_path, 'r', encoding='utf-8') as f:
    api = f.read()

# 1. Update save_visit
target_visit = """$lat = $_POST['latitude'] ?? null;
            $lon = $_POST['longitude'] ?? null;
            $stmt = $db->prepare("INSERT INTO field_visits (visit_date, executive_name, person_name, mobile, alt_mobile, profession, custom_profession, firm_name, state, city, pincode, full_address, lead_quality, photo_path, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $vd, $ex, $pn, $mob, $alt, $prof, $cprof, $firm, $state, $city, $pin, $addr, $qual, $pp, $lat, $lon
            ]);"""

repl_visit = """$lat = $_POST['latitude'] ?? null;
            $lon = $_POST['longitude'] ?? null;
            $v_addr = $_POST['verified_address'] ?? null;
            $stmt = $db->prepare("INSERT INTO field_visits (visit_date, executive_name, person_name, mobile, alt_mobile, profession, custom_profession, firm_name, state, city, pincode, full_address, lead_quality, photo_path, latitude, longitude, verified_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $vd, $ex, $pn, $mob, $alt, $prof, $cprof, $firm, $state, $city, $pin, $addr, $qual, $pp, $lat, $lon, $v_addr
            ]);"""
if target_visit in api:
    api = api.replace(target_visit, repl_visit)

# 2. Update save_followup
target_followup = """$stmt = $db->prepare("INSERT INTO field_visit_followups (visit_id, remarks, next_meeting_date, added_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$vid, $rem, $nmd, $added_by]);"""
repl_followup = """$lat = $_POST['latitude'] ?? null;
            $lon = $_POST['longitude'] ?? null;
            $v_addr = $_POST['verified_address'] ?? null;
            $stmt = $db->prepare("INSERT INTO field_visit_followups (visit_id, remarks, next_meeting_date, added_by, latitude, longitude, verified_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$vid, $rem, $nmd, $added_by, $lat, $lon, $v_addr]);"""
if target_followup in api:
    api = api.replace(target_followup, repl_followup)

with open(api_path, 'w', encoding='utf-8') as f:
    f.write(api)
print("api.php updated successfully!")
