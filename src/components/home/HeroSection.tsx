import React from "react";
import { Button } from "@/components/ui/button";

interface HeroSectionProps {
  backgroundImage?: string;
  headline?: string;
  subheading?: string;
  ctaText?: string;
  onCtaClick?: () => void;
}

const HeroSection = ({
  backgroundImage = "https://images.unsplash.com/photo-1577722422778-eaab0909b1ed?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1920&q=80",
  headline = "Discover Authentic Cebu Art & Crafts",
  subheading = "Support local artists and bring home a piece of Cebu's rich cultural heritage",
  ctaText = "Explore Products",
  onCtaClick = () => console.log("Explore products clicked"),
}: HeroSectionProps) => {
  return (
    <div className="relative w-full h-[500px] bg-gray-100 overflow-hidden">
      {/* Background Image with Overlay */}
      <div className="absolute inset-0 w-full h-full">
        <img
          src={backgroundImage}
          alt="Cebu Art and Crafts"
          className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-black/40"></div>
      </div>

      {/* Content Container */}
      <div className="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-center items-start">
        <div className="max-w-2xl">
          <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-4 leading-tight">
            {headline}
          </h1>

          <p className="text-xl md:text-2xl text-white/90 mb-8">{subheading}</p>

          <Button
            size="lg"
            onClick={onCtaClick}
            className="text-base font-semibold px-8 py-6 h-auto"
          >
            {ctaText}
          </Button>
        </div>
      </div>

      {/* Decorative Element */}
      <div className="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-white/10 to-transparent"></div>
    </div>
  );
};

export default HeroSection;
