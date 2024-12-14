<?php

namespace App\Utility;

class Strings
{
    // General Error and Success Messages
    public static function GeneralError()
    {
        return 'An unexpected error occurred. Please try again later.';
    }

    public static function ActionSuccessful()
    {
        return 'Action completed successfully.';
    }

    public static function RecordNotFound()
    {
        return 'The requested record could not be found.';
    }

    public static function ValidationError()
    {
        return 'Some fields have validation errors. Please check and try again.';
    }

    // Product Management Messages
    public static function ProductAdded()
    {
        return 'Product added successfully.';
    }

    public static function ProductUpdated()
    {
        return 'Product updated successfully.';
    }

    public static function ProductDeleted()
    {
        return 'Product deleted successfully.';
    }

    public static function ProductNotAvailable()
    {
        return 'This product is currently not available.';
    }

    // Order Processing Messages
    public static function OrderPlaced()
    {
        return 'Your order has been placed successfully.';
    }

    public static function OrderCancelled()
    {
        return 'The order has been cancelled successfully.';
    }

    public static function OrderNotFound()
    {
        return 'The order could not be found.';
    }

    public static function PaymentSuccessful()
    {
        return 'Payment processed successfully. Thank you for your purchase!';
    }

    public static function PaymentFailed()
    {
        return 'Payment failed. Please check your details and try again.';
    }

    // Training Enrollment Messages
    public static function TrainingEnrollmentSuccess()
    {
        return 'You have successfully enrolled in the training program.';
    }

    public static function TrainingEnrollmentFailed()
    {
        return 'Enrollment failed. Please try again later.';
    }

    public static function TrainingSlotUnavailable()
    {
        return 'No available slots for the selected training program.';
    }

    // User Account Messages
    public static function AccountUpdated()
    {
        return 'Your account information has been updated successfully.';
    }

    public static function PasswordChanged()
    {
        return 'Password changed successfully.';
    }

    public static function OldPasswordIncorrect()
    {
        return 'The old password you entered is incorrect.';
    }

    public static function ProfilePictureUpdated()
    {
        return 'Profile picture updated successfully.';
    }
}

?>
