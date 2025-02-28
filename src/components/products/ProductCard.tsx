import React from "react";
import { ShoppingCart } from "lucide-react";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";

interface ProductCardProps {
  id?: string;
  image?: string;
  title?: string;
  price?: number;
  artist?: string;
  onClick?: () => void;
  onAddToCart?: () => void;
}

const ProductCard = ({
  id = "1",
  image = "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=500&q=80",
  title = "Cebu Traditional Painting",
  price = 2500,
  artist = "Maria Santos",
  onClick = () => console.log("Product clicked"),
  onAddToCart = () => console.log("Added to cart"),
}: ProductCardProps) => {
  return (
    <Card
      className="w-full max-w-[300px] h-[400px] flex flex-col overflow-hidden transition-all duration-300 hover:shadow-lg bg-white"
      onClick={onClick}
    >
      <div className="relative w-full h-[200px] overflow-hidden">
        <img
          src={image}
          alt={title}
          className="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
        />
      </div>

      <CardHeader className="pb-2">
        <CardTitle className="text-lg font-bold line-clamp-1">
          {title}
        </CardTitle>
        <p className="text-sm text-gray-600">by {artist}</p>
      </CardHeader>

      <CardContent className="flex-grow">
        <p className="text-xl font-bold text-primary">
          ₱{price.toLocaleString()}
        </p>
      </CardContent>

      <CardFooter className="pt-0">
        <Button
          className="w-full"
          onClick={(e) => {
            e.stopPropagation();
            onAddToCart();
          }}
        >
          <ShoppingCart className="mr-2 h-4 w-4" />
          Add to Cart
        </Button>
      </CardFooter>
    </Card>
  );
};

export default ProductCard;
