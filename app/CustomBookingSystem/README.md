# Leobo Custom Booking System

A comprehensive WordPress booking solution with real-time pricing, Pan Hospitality API integration, and ACF Pro support.

## 📁 Folder Structure

```
CustomBookingSystem/
├── init.php                           # Main initialization file
├── CustomBookingSystem.php            # Core booking system class
├── assets/
│   ├── js/
│   │   └── custom-booking-system.js   # Frontend JavaScript
│   └── css/
│       └── custom-booking-system.css  # Styling
├── includes/
│   └── BookingAvailability.php        # API integration
└── docs/
    ├── implementation-guide.md         # Complete documentation
    ├── setup-checklist.md             # Quick setup guide
    └── README.md                      # This file
```

## 🚀 Quick Start

1. **System is already initialized** - the `init.php` file is included in your theme's `functions.php`
2. **Install ACF Pro** if not already installed
3. **Configure ACF field groups** (see setup-checklist.md)
4. **Add booking form** to any page: `[leobo_custom_booking_form]`

## 📚 Documentation

- **Complete Guide**: `/docs/implementation-guide.md`
- **Setup Checklist**: `/docs/setup-checklist.md`

## ⚙️ Key Features

- ✅ **Real-time Pricing** with seasonal rates and packages
- ✅ **Pan Hospitality API** integration for availability
- ✅ **ACF Pro Integration** for flexible content management
- ✅ **Responsive Design** optimized for all devices
- ✅ **Admin Interface** for booking management
- ✅ **Email Notifications** for staff and guests
- ✅ **Database Storage** with status tracking

## 🔧 Usage

### Basic Implementation
```php
[leobo_custom_booking_form]
```

### With Options
```php
[leobo_custom_booking_form theme="dark" show_title="true"]
```

## 📞 Quick Support

1. Check `/docs/setup-checklist.md` for common setup issues
2. Review `/docs/implementation-guide.md` for detailed documentation
3. Check WordPress Admin → Bookings for system status

---

*Leobo Custom Booking System - Unified and organized for better maintainability*
