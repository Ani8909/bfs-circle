import os

file_path = r'c:\Users\pc\Downloads\client mgmt2\staff\add_visit.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add Mic UI to meeting_in_progress
old_meeting_ui = """                <div id="meeting_in_progress" style="display:none;">
                    <div style="font-size:13px; color:#10b981; font-weight:600; margin-bottom:8px; display:flex; align-items:center; justify-content:center; gap:6px;">
                        <span style="width:8px; height:8px; background:#10b981; border-radius:50%; display:inline-block; animation: pulse 1.5s infinite;"></span> Meeting in progress...
                    </div>
                    <div id="visit_timer" style="font-size:36px; font-weight:800; color:#0f172a; margin-bottom:24px; font-variant-numeric: tabular-nums;">00:00:00</div>"""

new_meeting_ui = """                <div id="meeting_in_progress" style="display:none;">
                    <div style="font-size:13px; color:#10b981; font-weight:600; margin-bottom:8px; display:flex; align-items:center; justify-content:center; gap:6px;">
                        <span style="width:8px; height:8px; background:#10b981; border-radius:50%; display:inline-block; animation: pulse 1.5s infinite;"></span> Meeting in progress...
                    </div>
                    
                    <!-- Voice Recording Indicator -->
                    <div style="margin-bottom:15px; display:flex; align-items:center; justify-content:center; gap:8px; color:#ef4444; font-weight:600; font-size:14px;">
                        <i class="fas fa-microphone" style="animation: pulse 1s infinite;"></i> <span id="recording_status">Recording meeting audio...</span>
                    </div>

                    <div id="visit_timer" style="font-size:36px; font-weight:800; color:#0f172a; margin-bottom:24px; font-variant-numeric: tabular-nums;">00:00:00</div>"""

content = content.replace(old_meeting_ui, new_meeting_ui)


# 2. Block Gallery, Enforce Live Camera
old_file_input = """<input type="file" id="photo" name="photo" accept="image/*" onchange="previewImage(this)" style="width:100%; padding:12px; border:1px dashed #cbd5e1; border-radius:10px; background:#f8fafc; cursor:pointer;">"""
new_file_input = """<input type="file" id="photo" name="photo" accept="image/*" capture="environment" onchange="previewImage(this)" style="width:100%; padding:12px; border:1px dashed #cbd5e1; border-radius:10px; background:#f8fafc; cursor:pointer;">
<div style="font-size:11px; color:#ef4444; margin-top:4px;"><i class="fas fa-exclamation-triangle"></i> Live photo is mandatory (Gallery blocked).</div>"""
content = content.replace(old_file_input, new_file_input)


# 3. Javascript for MediaRecorder and Geo-fencing calculation
js_target = """        let checkInInterval;
        let checkInTimeObj = null;"""

js_repl = """        let checkInInterval;
        let checkInTimeObj = null;
        let mediaRecorder = null;
        let audioChunks = [];
        let audioBlob = null;
        let checkInLat = null;
        let checkInLon = null;

        // Haversine distance formula
        function getDistanceFromLatLonInM(lat1, lon1, lat2, lon2) {
          var R = 6371; // Radius of the earth in km
          var dLat = (lat2-lat1) * (Math.PI/180);
          var dLon = (lon2-lon1) * (Math.PI/180); 
          var a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * (Math.PI/180)) * Math.cos(lat2 * (Math.PI/180)) * 
            Math.sin(dLon/2) * Math.sin(dLon/2)
            ; 
          var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
          var d = R * c; // Distance in km
          return Math.round(d * 1000); // Distance in meters
        }"""
content = content.replace(js_target, js_repl)


start_checkin_target = """        function startCheckIn() {"""
start_checkin_repl = """        async function startCheckIn() {
            // Voice Recording Request
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                mediaRecorder.ondataavailable = e => {
                    if(e.data.size > 0) audioChunks.push(e.data);
                };
                mediaRecorder.start();
                document.getElementById('recording_status').innerText = "Recording meeting audio...";
            } catch (err) {
                console.error("Mic error:", err);
                document.getElementById('recording_status').innerHTML = "Mic Access Denied (Recording Disabled)";
                document.getElementById('recording_status').style.color = "#f59e0b";
            }"""
content = content.replace(start_checkin_target, start_checkin_repl)

geo_target = """                navigator.geolocation.getCurrentPosition(pos => {
                    document.getElementById('lat').value = pos.coords.latitude;
                    document.getElementById('lon').value = pos.coords.longitude;
                });"""
geo_repl = """                navigator.geolocation.getCurrentPosition(pos => {
                    checkInLat = pos.coords.latitude;
                    checkInLon = pos.coords.longitude;
                    document.getElementById('lat').value = checkInLat;
                    document.getElementById('lon').value = checkInLon;
                });"""
content = content.replace(geo_target, geo_repl)


end_checkin_target = """        function endCheckIn() {"""
end_checkin_repl = """        function endCheckIn() {
            // Stop Voice Recording
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.onstop = () => {
                    audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                };
                mediaRecorder.stop();
                // Stop all tracks to release mic
                mediaRecorder.stream.getTracks().forEach(t => t.stop());
            }

            // Geo-fence check (if checkIn location was captured)
            if (navigator.geolocation && checkInLat !== null) {
                navigator.geolocation.getCurrentPosition(pos => {
                    const checkOutLat = pos.coords.latitude;
                    const checkOutLon = pos.coords.longitude;
                    const distanceMeters = getDistanceFromLatLonInM(checkInLat, checkInLon, checkOutLat, checkOutLon);
                    
                    if (distanceMeters > 150) {
                        const remarksBox = document.getElementById('remarks');
                        remarksBox.value = `[SYSTEM ALERT: Staff checked-out ${distanceMeters}m away from check-in location]\\n` + remarksBox.value;
                    }
                });
            }"""
content = content.replace(end_checkin_target, end_checkin_repl)


# Append audio blob to form
submit_target = """            const form = document.getElementById('fieldVisitForm');
            const formData = new FormData(form);"""
submit_repl = """            const form = document.getElementById('fieldVisitForm');
            const formData = new FormData(form);
            if (audioBlob) {
                formData.append('audio_file', audioBlob, 'meeting_recording.webm');
            }"""
content = content.replace(submit_target, submit_repl)


with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated add_visit.php with advanced features")
