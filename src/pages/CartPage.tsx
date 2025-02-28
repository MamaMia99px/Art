import React from "react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import CartItem from "@/components/cart/CartItem";
import CartSummary from "@/components/cart/CartSummary";
import { Button } from "@/components/ui/button";
import { ShoppingCart, ArrowLeft } from "lucide-react";
import { Link } from "react-router-dom";

interface CartItem {
  id: string;
  image: string;
  title: string;
  price: number;
  quantity: number;
  artist: string;
}

const CartPage = () => {
  // Mock cart items
  const [cartItems, setCartItems] = React.useState<CartItem[]>([
    {
      id: "1",
      image:
        "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Cebu Traditional Painting",
      price: 2500,
      quantity: 1,
      artist: "Maria Santos",
    },
    {
      id: "2",
      image:
        "https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      title: "Handcrafted Wooden Sculpture",
      price: 3800,
      quantity: 1,
      artist: "Juan Reyes",
    },
  ]);

  const removeItem = (id: string) => {
    setCartItems(cartItems.filter((item) => item.id !== id));
  };

  const updateQuantity = (id: string, quantity: number) => {
    setCartItems(
      cartItems.map((item) => (item.id === id ? { ...item, quantity } : item)),
    );
  };

  // Calculate totals
  const subtotal = cartItems.reduce(
    (total, item) => total + item.price * item.quantity,
    0,
  );
  const shipping = 250;
  const tax = Math.round(subtotal * 0.12); // 12% tax
  const total = subtotal + shipping + tax;

  return (
    <div className="min-h-screen flex flex-col bg-gray-50">
      <Navbar
        cartItemCount={cartItems.reduce(
          (count, item) => count + item.quantity,
          0,
        )}
      />
      <main className="flex-grow py-8">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between mb-8">
            <h1 className="text-2xl font-bold">Shopping Cart</h1>
            <Link to="/">
              <Button variant="outline">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Continue Shopping
              </Button>
            </Link>
          </div>

          {cartItems.length > 0 ? (
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <div className="lg:col-span-2">
                <div className="bg-white rounded-lg shadow-sm p-6">
                  {cartItems.map((item) => (
                    <CartItem
                      key={item.id}
                      id={item.id}
                      image={item.image}
                      title={item.title}
                      price={item.price}
                      quantity={item.quantity}
                      artist={item.artist}
                      onRemove={() => removeItem(item.id)}
                      onUpdateQuantity={updateQuantity}
                    />
                  ))}
                </div>
              </div>

              <div>
                <CartSummary
                  subtotal={subtotal}
                  shipping={shipping}
                  tax={tax}
                  total={total}
                />
              </div>
            </div>
          ) : (
            <div className="text-center py-16 bg-white rounded-lg shadow-sm">
              <ShoppingCart className="h-16 w-16 mx-auto text-gray-400 mb-4" />
              <h2 className="text-2xl font-medium mb-2">Your cart is empty</h2>
              <p className="text-gray-600 mb-6">
                Looks like you haven't added any items to your cart yet.
              </p>
              <Link to="/">
                <Button>
                  <ArrowLeft className="mr-2 h-4 w-4" />
                  Start Shopping
                </Button>
              </Link>
            </div>
          )}
        </div>
      </main>
      <Footer />
    </div>
  );
};

export default CartPage;
