import React from "react";
import { Trash2, Minus, Plus } from "lucide-react";
import { Button } from "@/components/ui/button";

interface CartItemProps {
  id?: string;
  image?: string;
  title?: string;
  price?: number;
  quantity?: number;
  artist?: string;
  onRemove?: () => void;
  onUpdateQuantity?: (id: string, quantity: number) => void;
}

const CartItem = ({
  id = "1",
  image = "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
  title = "Cebu Traditional Painting",
  price = 2500,
  quantity = 1,
  artist = "Maria Santos",
  onRemove = () => console.log("Remove item"),
  onUpdateQuantity = (id, quantity) =>
    console.log(`Update quantity: ${id}, ${quantity}`),
}: CartItemProps) => {
  const decreaseQuantity = () => {
    if (quantity > 1) {
      onUpdateQuantity(id, quantity - 1);
    }
  };

  const increaseQuantity = () => {
    onUpdateQuantity(id, quantity + 1);
  };

  return (
    <div className="flex items-start space-x-4 py-6 border-b border-gray-200">
      {/* Product Image */}
      <div className="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
        <img
          src={image}
          alt={title}
          className="h-full w-full object-cover object-center"
        />
      </div>

      {/* Product Details */}
      <div className="flex-1 min-w-0">
        <h3 className="text-base font-medium text-gray-900">{title}</h3>
        <p className="mt-1 text-sm text-gray-500">by {artist}</p>
        <p className="mt-1 text-sm font-medium text-gray-900">
          ₱{price.toLocaleString()}
        </p>
      </div>

      {/* Quantity Controls */}
      <div className="flex items-center space-x-2">
        <Button
          variant="outline"
          size="icon"
          className="h-8 w-8"
          onClick={decreaseQuantity}
          disabled={quantity <= 1}
        >
          <Minus className="h-3 w-3" />
        </Button>
        <span className="w-8 text-center">{quantity}</span>
        <Button
          variant="outline"
          size="icon"
          className="h-8 w-8"
          onClick={increaseQuantity}
        >
          <Plus className="h-3 w-3" />
        </Button>
      </div>

      {/* Subtotal */}
      <div className="text-right">
        <p className="text-base font-medium text-gray-900">
          ₱{(price * quantity).toLocaleString()}
        </p>
      </div>

      {/* Remove Button */}
      <Button
        variant="ghost"
        size="icon"
        className="text-gray-500 hover:text-red-500"
        onClick={onRemove}
      >
        <Trash2 className="h-5 w-5" />
      </Button>
    </div>
  );
};

export default CartItem;
