import React, { useState } from "react";
import { Heart, ShoppingCart, Share2, Star } from "lucide-react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Separator } from "@/components/ui/separator";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";

interface ProductDetailProps {
  id?: string;
  title?: string;
  price?: number;
  description?: string;
  images?: string[];
  artist?: {
    name: string;
    location: string;
    image: string;
  };
  category?: string;
  rating?: number;
  reviews?: number;
  inStock?: boolean;
  onAddToCart?: (quantity: number) => void;
}

const ProductDetail = ({
  id = "1",
  title = "Cebu Traditional Painting",
  price = 2500,
  description = "A beautiful traditional painting showcasing the vibrant culture and landscapes of Cebu. This artwork captures the essence of Cebuano heritage with intricate details and vivid colors. Each brushstroke tells a story of the island's rich history and natural beauty.",
  images = [
    "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
    "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
    "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80",
  ],
  artist = {
    name: "Maria Santos",
    location: "Cebu City",
    image: "https://api.dicebear.com/7.x/avataaars/svg?seed=Maria",
  },
  category = "Painting",
  rating = 4.8,
  reviews = 24,
  inStock = true,
  onAddToCart = (quantity) => console.log(`Added ${quantity} to cart`),
}: ProductDetailProps) => {
  const [selectedImage, setSelectedImage] = useState(images[0]);
  const [quantity, setQuantity] = useState(1);

  const handleQuantityChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = parseInt(e.target.value);
    if (!isNaN(value) && value > 0) {
      setQuantity(value);
    }
  };

  const decreaseQuantity = () => {
    if (quantity > 1) {
      setQuantity(quantity - 1);
    }
  };

  const increaseQuantity = () => {
    setQuantity(quantity + 1);
  };

  return (
    <div className="w-full max-w-7xl mx-auto px-4 py-8 bg-white">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        {/* Product Images */}
        <div className="space-y-4">
          <div className="aspect-square overflow-hidden rounded-lg border border-gray-200">
            <img
              src={selectedImage}
              alt={title}
              className="w-full h-full object-cover"
            />
          </div>
          <div className="flex space-x-4 overflow-x-auto pb-2">
            {images.map((image, index) => (
              <div
                key={index}
                className={`w-20 h-20 rounded-md overflow-hidden border-2 cursor-pointer ${selectedImage === image ? "border-primary" : "border-gray-200"}`}
                onClick={() => setSelectedImage(image)}
              >
                <img
                  src={image}
                  alt={`${title} - view ${index + 1}`}
                  className="w-full h-full object-cover"
                />
              </div>
            ))}
          </div>
        </div>

        {/* Product Info */}
        <div className="space-y-6">
          <div>
            <Badge variant="outline" className="mb-2">
              {category}
            </Badge>
            <h1 className="text-3xl font-bold">{title}</h1>
            <div className="flex items-center mt-2 space-x-2">
              <div className="flex items-center">
                {[...Array(5)].map((_, i) => (
                  <Star
                    key={i}
                    className={`h-4 w-4 ${i < Math.floor(rating) ? "text-yellow-400 fill-yellow-400" : "text-gray-300"}`}
                  />
                ))}
              </div>
              <span className="text-sm text-gray-600">
                {rating} ({reviews} reviews)
              </span>
            </div>
          </div>

          <div className="flex items-center space-x-4">
            <div className="h-10 w-10 rounded-full overflow-hidden">
              <img
                src={artist.image}
                alt={artist.name}
                className="h-full w-full object-cover"
              />
            </div>
            <div>
              <p className="font-medium">Artist: {artist.name}</p>
              <p className="text-sm text-gray-600">{artist.location}</p>
            </div>
          </div>

          <Separator />

          <div>
            <p className="text-3xl font-bold text-primary">
              ₱{price.toLocaleString()}
            </p>
            <p className="text-sm text-gray-600 mt-1">
              {inStock ? "In Stock" : "Out of Stock"}
            </p>
          </div>

          <div className="flex items-center space-x-4">
            <div className="flex items-center">
              <Button
                variant="outline"
                size="icon"
                onClick={decreaseQuantity}
                disabled={quantity <= 1}
              >
                -
              </Button>
              <Input
                type="number"
                value={quantity}
                onChange={handleQuantityChange}
                min="1"
                className="w-16 text-center mx-2"
              />
              <Button variant="outline" size="icon" onClick={increaseQuantity}>
                +
              </Button>
            </div>

            <Button
              className="flex-1"
              onClick={() => onAddToCart(quantity)}
              disabled={!inStock}
            >
              <ShoppingCart className="mr-2 h-4 w-4" />
              Add to Cart
            </Button>

            <Button variant="outline" size="icon">
              <Heart className="h-5 w-5" />
            </Button>

            <Button variant="outline" size="icon">
              <Share2 className="h-5 w-5" />
            </Button>
          </div>

          <Tabs defaultValue="description">
            <TabsList className="grid w-full grid-cols-3">
              <TabsTrigger value="description">Description</TabsTrigger>
              <TabsTrigger value="details">Details</TabsTrigger>
              <TabsTrigger value="shipping">Shipping</TabsTrigger>
            </TabsList>
            <TabsContent value="description" className="pt-4">
              <Card>
                <CardContent className="pt-6">
                  <p className="text-gray-700">{description}</p>
                </CardContent>
              </Card>
            </TabsContent>
            <TabsContent value="details" className="pt-4">
              <Card>
                <CardContent className="pt-6">
                  <ul className="space-y-2">
                    <li>
                      <span className="font-medium">Medium:</span> Acrylic on
                      Canvas
                    </li>
                    <li>
                      <span className="font-medium">Dimensions:</span> 24" x 36"
                    </li>
                    <li>
                      <span className="font-medium">Created:</span> 2023
                    </li>
                    <li>
                      <span className="font-medium">Authenticity:</span> Signed
                      by artist, includes certificate
                    </li>
                  </ul>
                </CardContent>
              </Card>
            </TabsContent>
            <TabsContent value="shipping" className="pt-4">
              <Card>
                <CardContent className="pt-6">
                  <ul className="space-y-2">
                    <li>
                      <span className="font-medium">Delivery:</span> 3-5
                      business days within Cebu, 5-7 days nationwide
                    </li>
                    <li>
                      <span className="font-medium">Shipping Fee:</span> Free
                      shipping within Cebu, ₱250 nationwide
                    </li>
                    <li>
                      <span className="font-medium">Packaging:</span> Securely
                      packaged to prevent damage during transit
                    </li>
                    <li>
                      <span className="font-medium">Returns:</span> 7-day return
                      policy for damaged items
                    </li>
                  </ul>
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>
        </div>
      </div>
    </div>
  );
};

export default ProductDetail;
