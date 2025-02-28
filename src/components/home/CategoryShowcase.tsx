import React from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { ChevronLeft, ChevronRight } from "lucide-react";

interface Category {
  id: string;
  name: string;
  image: string;
  count?: number;
}

interface CategoryShowcaseProps {
  categories?: Category[];
  title?: string;
  onCategoryClick?: (categoryId: string) => void;
}

const CategoryShowcase = ({
  categories = [
    {
      id: "1",
      name: "Paintings",
      image:
        "https://images.unsplash.com/photo-1579783902614-a3fb3927b6a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      count: 42,
    },
    {
      id: "2",
      name: "Sculptures",
      image:
        "https://images.unsplash.com/photo-1544413164-5f1b295eb435?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      count: 28,
    },
    {
      id: "3",
      name: "Crafts",
      image:
        "https://images.unsplash.com/photo-1528396518501-b53b655eb9b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      count: 35,
    },
    {
      id: "4",
      name: "Food Products",
      image:
        "https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      count: 19,
    },
    {
      id: "5",
      name: "Jewelry",
      image:
        "https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      count: 23,
    },
    {
      id: "6",
      name: "Textiles",
      image:
        "https://images.unsplash.com/photo-1606722590583-6951b5ea92ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80",
      count: 17,
    },
  ],
  title = "Browse Categories",
  onCategoryClick = (id) => console.log(`Category clicked: ${id}`),
}: CategoryShowcaseProps) => {
  const scrollContainerRef = React.useRef<HTMLDivElement>(null);

  const scrollLeft = () => {
    if (scrollContainerRef.current) {
      scrollContainerRef.current.scrollBy({ left: -300, behavior: "smooth" });
    }
  };

  const scrollRight = () => {
    if (scrollContainerRef.current) {
      scrollContainerRef.current.scrollBy({ left: 300, behavior: "smooth" });
    }
  };

  return (
    <div className="w-full py-12 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-2xl font-bold text-gray-900">{title}</h2>
          <div className="flex gap-2">
            <Button
              variant="outline"
              size="icon"
              onClick={scrollLeft}
              className="rounded-full"
            >
              <ChevronLeft className="h-5 w-5" />
            </Button>
            <Button
              variant="outline"
              size="icon"
              onClick={scrollRight}
              className="rounded-full"
            >
              <ChevronRight className="h-5 w-5" />
            </Button>
          </div>
        </div>

        <div
          ref={scrollContainerRef}
          className="flex overflow-x-auto pb-4 gap-4 scrollbar-hide"
          style={{ scrollbarWidth: "none", msOverflowStyle: "none" }}
        >
          {categories.map((category) => (
            <Card
              key={category.id}
              className="min-w-[200px] cursor-pointer transition-all hover:shadow-md bg-white"
              onClick={() => onCategoryClick(category.id)}
            >
              <div className="relative h-[150px] overflow-hidden rounded-t-xl">
                <img
                  src={category.image}
                  alt={category.name}
                  className="w-full h-full object-cover transition-transform hover:scale-105 duration-300"
                />
              </div>
              <CardContent className="p-4">
                <h3 className="font-medium text-lg">{category.name}</h3>
                {category.count !== undefined && (
                  <p className="text-sm text-gray-500">
                    {category.count} items
                  </p>
                )}
              </CardContent>
            </Card>
          ))}
        </div>
      </div>
    </div>
  );
};

export default CategoryShowcase;
