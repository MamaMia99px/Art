import React from "react";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { Input } from "@/components/ui/input";

interface CartSummaryProps {
  subtotal?: number;
  shipping?: number;
  tax?: number;
  total?: number;
  onApplyCoupon?: (coupon: string) => void;
  onCheckout?: () => void;
}

const CartSummary = ({
  subtotal = 5000,
  shipping = 250,
  tax = 600,
  total = 5850,
  onApplyCoupon = (coupon) => console.log(`Apply coupon: ${coupon}`),
  onCheckout = () => console.log("Proceed to checkout"),
}: CartSummaryProps) => {
  const [couponCode, setCouponCode] = React.useState("");

  const handleApplyCoupon = () => {
    if (couponCode.trim()) {
      onApplyCoupon(couponCode);
    }
  };

  return (
    <div className="bg-gray-50 rounded-lg p-6 sticky top-24">
      <h2 className="text-lg font-medium text-gray-900 mb-4">Order Summary</h2>

      <div className="space-y-4">
        <div className="flex justify-between">
          <span className="text-gray-600">Subtotal</span>
          <span className="font-medium">₱{subtotal.toLocaleString()}</span>
        </div>

        <div className="flex justify-between">
          <span className="text-gray-600">Shipping</span>
          <span className="font-medium">₱{shipping.toLocaleString()}</span>
        </div>

        <div className="flex justify-between">
          <span className="text-gray-600">Tax</span>
          <span className="font-medium">₱{tax.toLocaleString()}</span>
        </div>

        <Separator />

        <div className="flex justify-between text-lg font-bold">
          <span>Total</span>
          <span>₱{total.toLocaleString()}</span>
        </div>

        <div className="pt-4">
          <div className="flex space-x-2 mb-4">
            <Input
              placeholder="Coupon code"
              value={couponCode}
              onChange={(e) => setCouponCode(e.target.value)}
            />
            <Button
              variant="outline"
              onClick={handleApplyCoupon}
              disabled={!couponCode.trim()}
            >
              Apply
            </Button>
          </div>

          <Button className="w-full" size="lg" onClick={onCheckout}>
            Proceed to Checkout
          </Button>
        </div>
      </div>
    </div>
  );
};

export default CartSummary;
