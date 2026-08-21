$js = @"

// Smart Amount Formatting
function formatAmount(input) {
    let val = input.value.replace(/\D/g, '');
    if (!val) {
        input.value = '';
        document.getElementById('amount_real').value = '';
        return;
    }
    document.getElementById('amount_real').value = val;
    let x = val.toString();
    let lastThree = x.substring(x.length-3);
    let otherNumbers = x.substring(0, x.length-3);
    if (otherNumbers != '') {
        lastThree = ',' + lastThree;
    }
    input.value = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree;
}

function prepareSubmit() {
    return true; 
}

const totalSteps = 3;
function nextStep(step) {
    const currentStepDiv = document.getElementById('step' + (step - 1));
    if(currentStepDiv) {
        const inputs = currentStepDiv.querySelectorAll('input[required], select[required], textarea[required]');
        for (let input of inputs) {
            if (!input.checkValidity()) {
                input.reportValidity();
                return;
            }
        }
    }
    document.querySelectorAll('.form-step').forEach(el => el.style.display = 'none');
    document.getElementById('step' + step).style.display = 'block';
    
    const pct = Math.round((step / totalSteps) * 100);
    document.getElementById('progress-bar').style.width = pct + '%';
    const titles = ["", "Personal Info", "Loan Details", "Address & Docs"];
    document.getElementById('step-text').innerText = 'Step ' + step + ' of 3: ' + titles[step];
    document.getElementById('step-percent').innerText = pct + '%';
}

function prevStep(step) {
    document.querySelectorAll('.form-step').forEach(el => el.style.display = 'none');
    document.getElementById('step' + step).style.display = 'block';
    
    const pct = Math.round((step / totalSteps) * 100);
    document.getElementById('progress-bar').style.width = pct + '%';
    const titles = ["", "Personal Info", "Loan Details", "Address & Docs"];
    document.getElementById('step-text').innerText = 'Step ' + step + ' of 3: ' + titles[step];
    document.getElementById('step-percent').innerText = pct + '%';
}
</script>
"@

$content = Get-Content -Raw "c:\Users\pc\Downloads\client mgmt2\apply.php"
$content = $content -replace "</script>\s*</body>", "$js`n</body>"

Set-Content -Path "c:\Users\pc\Downloads\client mgmt2\apply.php" -Value $content
