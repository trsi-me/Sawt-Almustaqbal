// صوت المستقبل - JavaScript الرئيسي

// تفعيل القائمة المتحركة على الأجهزة المحمولة
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navMenu.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
            
            // أنيميشن للعناصر عند الفتح
            if (navMenu.classList.contains('active')) {
                const navItems = navMenu.querySelectorAll('li');
                navItems.forEach((item, index) => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        item.style.transition = 'all 0.3s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateX(0)';
                    }, index * 50);
                });
            }
        });
    }

    // إغلاق القائمة عند النقر على رابط
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                navMenu.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            }
        });
    });

    // إغلاق القائمة عند النقر خارجها
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768) {
            if (!navMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                navMenu.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            }
        }
    });

    // أنيميشن للروابط عند التمرير
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // تحميل التعليقات عند تحميل الصفحة
    loadComments();
    
    // أنيميشن للكاردات عند التحميل
    animateCards();
});

// أنيميشن للكاردات
function animateCards() {
    const cards = document.querySelectorAll('.section-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(30px)';
                    entry.target.style.transition = 'all 0.6s ease';
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        observer.observe(card);
    });
}

// تحديد مسار API بناءً على موقع الصفحة
function getApiPath() {
    const path = window.location.pathname;
    if (path.includes('/pages/')) {
        return '../api/';
    }
    return 'api/';
}

// تحميل التعليقات من الخادم
function loadComments() {
    const commentsSection = document.querySelector('.comments-list');
    if (!commentsSection) return;

    const pageName = window.location.pathname.split('/').pop().replace('.html', '') || 'index';
    const apiPath = getApiPath();
    
    fetch(`${apiPath}get_comments.php?page=${pageName}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.comments) {
                displayComments(data.comments);
            }
        })
        .catch(error => {
            console.error('خطأ في تحميل التعليقات:', error);
        });
}

// عرض التعليقات
function displayComments(comments) {
    const commentsList = document.querySelector('.comments-list');
    if (!commentsList) return;

    if (comments.length === 0) {
        commentsList.innerHTML = '<p style="color: var(--text-secondary); text-align: center;">لا توجد تعليقات بعد. كن أول من يعلق!</p>';
        return;
    }

    commentsList.innerHTML = comments.map((comment, index) => {
        const delay = index * 100;
        return `
        <div class="comment-item" style="animation-delay: ${delay}ms;">
            <div class="comment-author">${escapeHtml(comment.name)}</div>
            <div class="comment-date">${formatDate(comment.created_at)}</div>
            <div class="comment-text">${escapeHtml(comment.comment)}</div>
        </div>
    `;
    }).join('');
}

// إرسال تعليق جديد
function submitComment(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const pageName = window.location.pathname.split('/').pop().replace('.html', '') || 'index';
    formData.append('page', pageName);
    const apiPath = getApiPath();

    fetch(`${apiPath}submit_comment.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            form.reset();
            loadComments();
            showMessage('تم إرسال تعليقك بنجاح!', 'success');
        } else {
            showMessage(data.message || 'حدث خطأ أثناء إرسال التعليق', 'error');
        }
    })
    .catch(error => {
        console.error('خطأ:', error);
        showMessage('حدث خطأ أثناء إرسال التعليق', 'error');
    });
}

// عرض رسالة للمستخدم
function showMessage(message, type) {
    const messageDiv = document.createElement('div');
    const bgColor = type === 'success' ? '#98FB98' : '#FF6B6B';
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: ${bgColor};
        color: #333333;
        padding: 1rem 2rem;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 10000;
        font-weight: 500;
        opacity: 0;
        transform: translateX(100px);
        transition: all 0.3s ease;
    `;
    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);

    // أنيميشن الدخول
    setTimeout(() => {
        messageDiv.style.opacity = '1';
        messageDiv.style.transform = 'translateX(0)';
    }, 10);

    // أنيميشن الخروج
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        messageDiv.style.transform = 'translateX(100px)';
        setTimeout(() => {
            if (document.body.contains(messageDiv)) {
                document.body.removeChild(messageDiv);
            }
        }, 300);
    }, 3000);
}

// تنظيف النص من HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// تنسيق التاريخ
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('ar-SA', options);
}

