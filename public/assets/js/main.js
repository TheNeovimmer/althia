/**
 * MEDICASE - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {

    /* --- Navbar Scroll Effect --- */
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        const checkScroll = () => {
            if (window.scrollY > 20) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        };
        checkScroll();
        window.addEventListener('scroll', checkScroll);
    }

    /* --- Mobile Nav Toggle --- */
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.navbar-menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('show');
            const icon = navToggle.querySelector('i');
            if (icon) {
                icon.className = navMenu.classList.contains('show') ? 'fas fa-times' : 'fas fa-bars';
            }
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.navbar')) {
                navMenu.classList.remove('show');
                const icon = navToggle.querySelector('i');
                if (icon) icon.className = 'fas fa-bars';
            }
        });
    }

    /* --- Dashboard Sidebar Toggle --- */
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.dashboard-sidebar');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    if (sidebarToggle && sidebar && sidebarOverlay) {
        const openSidebar = () => {
            sidebar.classList.add('open');
            sidebarOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        };
        const closeSidebar = () => {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
            document.body.style.overflow = '';
        };
        sidebarToggle.addEventListener('click', openSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    /* --- Nav Dropdown Toggle --- */
    const navUser = document.querySelector('.nav-user');
    const navDropdown = document.querySelector('.nav-dropdown');
    if (navUser && navDropdown) {
        navUser.addEventListener('click', (e) => {
            e.stopPropagation();
            navDropdown.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            navDropdown.classList.remove('show');
        });
    }

    /* --- Flash Message Auto-Dismiss --- */
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(msg => {
        setTimeout(() => {
            msg.style.opacity = '0';
            msg.style.transform = 'translateY(-10px)';
            setTimeout(() => msg.remove(), 300);
        }, 5000);
    });

    /* --- Smooth Scroll for Anchor Links --- */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', (e) => {
            const target = document.querySelector(anchor.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* --- Form Validation --- */
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            let valid = true;
            form.querySelectorAll('[required]').forEach(field => {
                const errorEl = field.closest('.form-group').querySelector('.field-error');
                if (errorEl) errorEl.remove();

                if (!field.value.trim()) {
                    const error = document.createElement('span');
                    error.className = 'field-error';
                    error.textContent = 'This field is required';
                    field.closest('.form-group').appendChild(error);
                    field.style.borderColor = '#dc3545';
                    valid = false;
                } else {
                    field.style.borderColor = '';
                }

                if (field.type === 'email' && field.value && !field.value.includes('@')) {
                    const error = document.createElement('span');
                    error.className = 'field-error';
                    error.textContent = 'Please enter a valid email';
                    field.closest('.form-group').appendChild(error);
                    field.style.borderColor = '#dc3545';
                    valid = false;
                }

                if (field.name === 'password_confirm' || field.id === 'confirm_password') {
                    const password = form.querySelector('[name="new_password"], [name="password"]');
                    if (password && field.value !== password.value) {
                        const error = document.createElement('span');
                        error.className = 'field-error';
                        error.textContent = 'Passwords do not match';
                        field.closest('.form-group').appendChild(error);
                        field.style.borderColor = '#dc3545';
                        valid = false;
                    }
                }
            });

            if (!valid) e.preventDefault();
        });
    });

    /* --- Password Toggle Visibility --- */
    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.form-group').querySelector('input');
            if (input) {
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                btn.querySelector('i').className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
            }
        });
    });

    /* --- Delete Confirmation --- */
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    /* --- Notification Badge Count --- */
    const notifCount = document.querySelector('.notif-count');
    if (notifCount) {
        const count = parseInt(notifCount.textContent);
        if (count > 0) {
            notifCount.style.display = 'flex';
        }
    }

    /* --- Scroll Animations (Intersection Observer) --- */
    if ('IntersectionObserver' in window) {
        const animElements = document.querySelectorAll('.animate-on-scroll');
        if (animElements.length) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animated');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            animElements.forEach(el => observer.observe(el));
        }
    }

    /* --- Modal Controls --- */
    document.querySelectorAll('[data-modal]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.dataset.modal;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal || e.target.closest('.modal-close')) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('show')) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });

    /* --- Active Nav Link --- */
    const currentPath = window.location.pathname;
    document.querySelectorAll('.navbar-menu a').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.startsWith(href) && href !== '/') {
            link.classList.add('active');
        } else if (href === '/' && currentPath === '/') {
            link.classList.add('active');
        }
    });

    /* --- FAQ Accordion --- */
    document.querySelectorAll('.faq-question').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.faq-accordion-item');
            const isActive = item.classList.contains('active');
            const accordion = item.closest('.faq-accordion');
            accordion.querySelectorAll('.faq-accordion-item').forEach(el => el.classList.remove('active'));
            if (!isActive) item.classList.add('active');
        });
    });

    /* --- AI Chat Widget --- */
    const chatBtn = document.getElementById('chatWidgetBtn');
    const chatPanel = document.getElementById('chatWidgetPanel');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const chatSendBtn = document.getElementById('chatSendBtn');

    if (chatBtn && chatPanel && chatMessages && chatInput && chatSendBtn) {
        chatBtn.addEventListener('click', () => {
            const isOpen = chatPanel.style.display === 'flex';
            chatPanel.style.display = isOpen ? 'none' : 'flex';
            if (isOpen) {
                chatBtn.querySelector('.chat-btn-icon').style.display = 'flex';
                chatBtn.querySelector('.chat-btn-close').style.display = 'none';
            } else {
                chatBtn.querySelector('.chat-btn-icon').style.display = 'none';
                chatBtn.querySelector('.chat-btn-close').style.display = 'flex';
                chatInput.focus();
            }
        });

        const appendMessage = (role, text) => {
            const welcome = chatMessages.querySelector('.welcome-message');
            if (welcome) welcome.remove();

            const div = document.createElement('div');
            div.className = 'message ' + role;
            if (role === 'assistant') {
                div.innerHTML = '<p>' + text.replace(/\n/g, '<br>') + '</p>';
            } else {
                div.textContent = text;
            }
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        };

        const showTyping = () => {
            const el = document.createElement('div');
            el.className = 'typing-indicator';
            el.id = 'typingIndicator';
            el.innerHTML = '<span></span><span></span><span></span>';
            chatMessages.appendChild(el);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        };

        const hideTyping = () => {
            const el = document.getElementById('typingIndicator');
            if (el) el.remove();
        };

        const sendMessage = async () => {
            const text = chatInput.value.trim();
            if (!text) return;

            chatInput.value = '';
            chatSendBtn.disabled = true;
            appendMessage('user', text);
            showTyping();

            try {
                const res = await fetch('/api/ai/public-chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text }),
                });
                const data = await res.json();
                hideTyping();
                if (data.response) {
                    appendMessage('assistant', data.response);
                } else {
                    appendMessage('assistant', 'Sorry, I could not process your request. Please try again.');
                }
            } catch {
                hideTyping();
                appendMessage('assistant', 'Sorry, I encountered a connection issue. Please try again.');
            } finally {
                chatSendBtn.disabled = false;
                chatInput.focus();
            }
        };

        chatSendBtn.addEventListener('click', sendMessage);
        chatInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        /* --- Hero AI Quick Chat --- */
        const heroInput = document.getElementById('heroChatInput');
        const heroBtn = document.getElementById('heroChatBtn');

        const submitFromHero = () => {
            const text = heroInput.value.trim();
            if (!text) return;
            chatPanel.style.display = 'flex';
            chatBtn.querySelector('.chat-btn-icon').style.display = 'none';
            chatBtn.querySelector('.chat-btn-close').style.display = 'flex';
            chatInput.value = text;
            heroInput.value = '';
            sendMessage();
        };

        if (heroInput && heroBtn) {
            heroBtn.addEventListener('click', submitFromHero);
            heroInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitFromHero();
                }
            });
        }
    }

});
