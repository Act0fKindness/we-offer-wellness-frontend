# Email Trigger Matrix

This project now covers the most common ecommerce scenarios. Each template lives under `resources/views/emails` and is sent through `App\Services\TransactionalMail`.

## Marketing & Opt-ins
| Template | View | Trigger |
| --- | --- | --- |
| Confirm subscription | `emails/subscriber-confirm.blade.php` | `V3SubscriberController@store` whenever a new/returning subscriber needs double opt-in. |
| Welcome / subscription confirmed | `emails/subscriber-welcome.blade.php` | `SubscriberController@confirm` after a confirmation token is redeemed. |
| Preference prompt | `emails/subscriber-preferences.blade.php` | `SubscriberController@confirm` immediately after the welcome email. |
| Preferences updated | `emails/subscriber-preferences-updated.blade.php` | `SubscriberController@updatePreferences` after saving interest/goals. |
| Unsubscribe confirmation | `emails/subscriber-unsubscribed.blade.php` | `SubscriberController@unsubscribe`. |
| Resubscribe confirmation | `emails/subscriber-resubscribed.blade.php` | `SubscriberController@resubscribe`. |

## Account & Security
| Template | View | Trigger |
| --- | --- | --- |
| Account welcome | `emails/account-welcome.blade.php` | `RegisteredUserController@store` after firing the `Registered` event. |
| Email verification code | `emails/verify-email.blade.php` | Existing behaviour in `User::sendEmailVerificationNotification()`. |
| Password reset CTA | `emails/reset-password.blade.php` | `User::sendPasswordResetNotification()`. |
| Password changed confirmation | `emails/password-changed.blade.php` | Both `PasswordController@update` and `NewPasswordController@store`. |
| Email changed confirmation/alert | `emails/email-changed.blade.php` | `ProfileController@update` when the email field changes (goes to old + new inboxes). |
| Login alert | `emails/login-alert.blade.php` | `LoginSecurityService::recordLogin()` from `AuthenticatedSessionController@store` when a new device/fingerprint logs in. |
| Account deletion confirmation | `emails/account-deleted.blade.php` | `ProfileController@destroy`. |

## Purchase & Payment
| Template | View | Trigger |
| --- | --- | --- |
| Order confirmation / receipt | `emails/order-confirmation.blade.php` | `CheckoutOrderService::finalizePaidAttempt()` and (fallback) the Stripe webhook when an existing order transitions to `paid`. |
| Payment failed | `emails/payment-failed.blade.php` | Stripe webhooks `checkout.session.async_payment_failed` and `payment_intent.payment_failed`. |
| Refund confirmation | `emails/refund-confirmation.blade.php` | Stripe webhook `charge.refunded`. |
| Dispute/chargeback received | `emails/dispute-notice.blade.php` | Stripe webhook `charge.dispute.created`. |

Each template reuses the shared `emails.layout` styling so future additions simply need a new Blade view and a call through `TransactionalMail`.
