var step = 1;
$(document).ready(function () { stepProgress(step); });

$(".next").on("click", function () {
    var nextstep = true;

    if (step == 2) {
        nextstep = checkForm("userinfo");  
    } 
    if (step == 3) {
        nextstep = checkForm("photos"); 
    } 
    if (step == 4) {
        nextstep = checkForm("description"); 
    }

    if (nextstep == true) {
        if (step < $(".step").length) {
            $(".step").show();
            $(".step")
            .not(":eq(" + step++ + ")")
            .hide();
            stepProgress(step);
        }
        hideButtons(step);
    }
});


$(".back").on("click", function () {
	if (step > 1) {
		step = step - 2;
		$(".next").trigger("click");
	}
	hideButtons(step);
});

stepProgress = function (currstep) {
	var percent = parseFloat(100 / $(".step").length) * currstep;
	percent = percent.toFixed();
	$(".progress-bar")
	.css("width", percent + "%")
	.html(percent + "%");
};

hideButtons = function (step) {
	var limit = parseInt($(".step").length);
	$(".action").hide();
	if (step < limit) {
		$(".next").show();
	}
	if (step > 1) {
		$(".back").show();
	}
	if (step == limit) {
		$(".next").hide();
		$(".submit").show();
	}
};

function checkForm(val) {
    var valid = true;
    $("#" + val + " input:required, #" + val + " textarea:required").each(function () {
        if ($(this).val() === "") {
            $(this).addClass("is-invalid");
            valid = false;
        } else {
            $(this).removeClass("is-invalid");
        }
		
        if ($(this).attr("type") === "email") {
            var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailPattern.test($(this).val())) {
                $(this).addClass("is-invalid");
                valid = false;
            } else {
                $(this).removeClass("is-invalid");
            }
        }
		
    });
    return valid;
}
