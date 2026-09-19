if (window.jQuery) {
(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner(0);
    
    
    // Initiate the wowjs
    if (window.WOW) {
        new WOW().init();
    }


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 200) {
            $('.sticky-top').addClass('shadow-sm').css('top', '0px');
        } else {
            $('.sticky-top').removeClass('shadow-sm').css('top', '-100px');
        }
    });


    // Car Categories
    if ($.fn.owlCarousel) {
        $(".categories-carousel").owlCarousel({
            autoplay: true,
            autoplayTimeout: 3500,
            smartSpeed: 1000,
            dots: false,
            loop: true,
            margin: 25,
            nav : true,
            navText : [
                '<i class="fas fa-chevron-left"></i>',
                '<i class="fas fa-chevron-right"></i>'
            ],
            responsiveClass: true,
            responsive: {
                0:{
                    items:1
                },
                576:{
                    items:1
                },
                768:{
                    items:1
                },
                992:{
                    items:2
                },
                1200:{
                    items:3
                }
            }
        });
    }

   // Back to top button
   $(window).scroll(function () {
    if ($(this).scrollTop() > 300) {
        $('.back-to-top').fadeIn('slow');
    } else {
        $('.back-to-top').fadeOut('slow');
    }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


})(window.jQuery);
}

(function () {
    "use strict";

    var widget = document.querySelector("[data-ai-chat]");
    if (!widget) {
        return;
    }

    var toggle = widget.querySelector(".ai-chat-toggle");
    var panel = widget.querySelector(".ai-chat-panel");
    var close = widget.querySelector(".ai-chat-close");
    var body = widget.querySelector(".ai-chat-body");
    var form = widget.querySelector(".ai-chat-form");
    var input = form ? form.querySelector("input") : null;
    var suggestions = widget.querySelectorAll("[data-ai-question]");

    var endpoint = "/Carrental/CarRental_Backend/api/chatbot/chat.php";

    function setOpen(isOpen) {
        panel.hidden = !isOpen;
        toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        if (isOpen && input) {
            setTimeout(function () {
                input.focus();
            }, 120);
        }
    }

    function addMessage(text, type) {
        var message = document.createElement("div");
        var bubble = document.createElement("span");
        message.className = "ai-message ai-message-" + type;
        bubble.textContent = text;
        message.appendChild(bubble);
        body.appendChild(message);
        body.scrollTop = body.scrollHeight;
        return message;
    }

    function addBotResponse(text, data) {
        var message = document.createElement("div");
        var bubble = document.createElement("div");
        var reply = document.createElement("span");
        message.className = "ai-message ai-message-bot ai-message-rich";
        bubble.className = "ai-response";
        reply.textContent = text;
        bubble.appendChild(reply);

        if (data && data.type === "cars" && Array.isArray(data.items)) {
            bubble.appendChild(renderCarCards(data.items));
        }

        if (data && data.type === "bookings" && Array.isArray(data.items)) {
            bubble.appendChild(renderInfoCards(data.items, "booking"));
        }

        if (data && data.type === "payments" && Array.isArray(data.items)) {
            bubble.appendChild(renderInfoCards(data.items, "payment"));
        }

        message.appendChild(bubble);
        body.appendChild(message);
        body.scrollTop = body.scrollHeight;
        return message;
    }

    function renderCarCards(cars) {
        var list = document.createElement("div");
        list.className = "ai-card-list ai-car-list";

        cars.forEach(function (car) {
            var card = document.createElement("a");
            card.className = "ai-car-card";
            card.href = car.url || "/Carrental/vehicle";

            var imageWrap = document.createElement("div");
            imageWrap.className = "ai-car-image";
            var image = document.createElement("img");
            image.src = car.image || "/Carrental/public/frontend/assets/img/cars/car-1.png";
            image.alt = car.name || "Xe cho thuê";
            image.loading = "lazy";
            imageWrap.appendChild(image);

            var content = document.createElement("div");
            content.className = "ai-car-content";

            var name = document.createElement("strong");
            name.className = "ai-car-name";
            name.textContent = car.name || "Xe cho thuê";

            var metaTop = document.createElement("div");
            metaTop.className = "ai-car-status";
            metaTop.textContent = car.status || "Đang cập nhật";

            var price = document.createElement("div");
            price.className = "ai-car-price";
            price.textContent = car.priceText || "";

            var specs = document.createElement("div");
            specs.className = "ai-car-specs";
            specs.appendChild(specItem("fas fa-users", (car.seats || 0) + " ghế"));
            specs.appendChild(specItem("fas fa-cogs", car.transmission || ""));
            specs.appendChild(specItem("fas fa-gas-pump", car.fuelType || ""));

            var action = document.createElement("span");
            action.className = "ai-card-action";
            action.textContent = "Xem chi tiết";

            content.appendChild(name);
            content.appendChild(metaTop);
            content.appendChild(price);
            content.appendChild(specs);
            content.appendChild(action);
            card.appendChild(imageWrap);
            card.appendChild(content);
            list.appendChild(card);
        });

        return list;
    }

    function renderInfoCards(items, kind) {
        var list = document.createElement("div");
        list.className = "ai-card-list";

        items.forEach(function (item) {
            var card = document.createElement("a");
            card.className = "ai-info-card";
            card.href = item.url || "#";

            var title = document.createElement("strong");
            title.textContent = item.title || "";

            var meta = document.createElement("div");
            meta.className = "ai-info-meta";
            meta.textContent = kind === "booking"
                ? [item.dateText, item.daysText].filter(Boolean).join(" - ")
                : [item.bookingText, item.type].filter(Boolean).join(" - ");

            var footer = document.createElement("div");
            footer.className = "ai-info-footer";

            var amount = document.createElement("span");
            amount.textContent = kind === "booking" ? item.priceText : item.amountText;

            var status = document.createElement("em");
            status.textContent = item.status || "";

            footer.appendChild(amount);
            footer.appendChild(status);
            card.appendChild(title);
            card.appendChild(meta);
            card.appendChild(footer);
            list.appendChild(card);
        });

        return list;
    }

    function specItem(iconClass, text) {
        var item = document.createElement("span");
        var icon = document.createElement("i");
        icon.className = iconClass;
        item.appendChild(icon);
        item.appendChild(document.createTextNode(text || "N/A"));
        return item;
    }

    function fetchAnswer(question) {
        return fetch(endpoint, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                message: question
            })
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data && data.reply) {
                    return data;
                }

                return {
                    reply: "Mình chưa nhận được câu trả lời phù hợp từ hệ thống.",
                    data: {}
                };
            })
            .catch(function () {
                return {
                    reply: "Hiện tại chatbot chưa kết nối được với dữ liệu website. Bạn vui lòng thử lại sau hoặc liên hệ hotline +01234567890.",
                    data: {}
                };
            });
    }

    function ask(question) {
        var text = question.trim();
        if (!text) {
            return;
        }

        addMessage(text, "user");
        if (input) {
            input.value = "";
        }

        var typing = addMessage("Đang tìm câu trả lời...", "bot ai-message-typing");
        fetchAnswer(text).then(function (answer) {
            typing.remove();
            addBotResponse(answer.reply, answer.data);
        });
    }

    toggle.addEventListener("click", function () {
        setOpen(panel.hidden);
    });

    close.addEventListener("click", function () {
        setOpen(false);
    });

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        ask(input.value);
    });

    suggestions.forEach(function (button) {
        button.addEventListener("click", function () {
            ask(button.getAttribute("data-ai-question") || button.textContent);
        });
    });
})();

