import React, { useEffect, useRef } from 'react';
import {
  View,
  Text,
  Image,
  Animated,
  Dimensions,
  StyleSheet,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import Svg, { Defs, Mask, Rect, Circle } from 'react-native-svg';
import { luxuryStyles } from '../styles/luxuryStyles';

const { width: screenWidth, height: screenHeight } = Dimensions.get('window');

export default function HeroSection() {
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(50)).current;
  const scaleAnim = useRef(new Animated.Value(0.8)).current;
  const maskAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    // Staggered animations for smooth reveal
    Animated.sequence([
      Animated.delay(300),
      Animated.parallel([
        Animated.timing(fadeAnim, {
          toValue: 1,
          duration: 1000,
          useNativeDriver: true,
        }),
        Animated.timing(slideAnim, {
          toValue: 0,
          duration: 800,
          useNativeDriver: true,
        }),
        Animated.timing(scaleAnim, {
          toValue: 1,
          duration: 1200,
          useNativeDriver: true,
        }),
      ]),
    ]).start();

    // Mask reveal animation
    Animated.timing(maskAnim, {
      toValue: 1,
      duration: 1500,
      useNativeDriver: false,
    }).start();
  }, []);

  const maskScale = maskAnim.interpolate({
    inputRange: [0, 1],
    outputRange: [0, 2],
  });

  return (
    <View style={luxuryStyles.heroSection}>
      {/* Background Image with Parallax */}
      <Image
        source={{
          uri: 'https://images.unsplash.com/photo-1556306535-38febf6782e7?w=800&h=1200&fit=crop',
        }}
        style={luxuryStyles.heroBackground}
        resizeMode="cover"
      />

      {/* Gradient Overlay */}
      <LinearGradient
        colors={['rgba(0,0,0,0.3)', 'rgba(0,0,0,0.7)', 'rgba(0,0,0,0.9)']}
        style={luxuryStyles.heroOverlay}
      />

      {/* SVG Mask for Reveal Effect */}
      <Animated.View style={[luxuryStyles.maskContainer, { transform: [{ scale: maskScale }] }]}>
        <Svg height="100%" width="100%" style={StyleSheet.absoluteFillObject}>
          <Defs>
            <Mask id="revealMask">
              <Rect width="100%" height="100%" fill="black" />
              <Circle cx="50%" cy="50%" r="40%" fill="white" />
            </Mask>
          </Defs>
          <Rect
            width="100%"
            height="100%"
            fill="rgba(212, 175, 55, 0.1)"
            mask="url(#revealMask)"
          />
        </Svg>
      </Animated.View>

      {/* Hero Content */}
      <Animated.View
        style={[
          luxuryStyles.heroContent,
          {
            opacity: fadeAnim,
            transform: [
              { translateY: slideAnim },
              { scale: scaleAnim },
            ],
          },
        ]}
      >
        <Text style={luxuryStyles.heroTitle}>LUXE</Text>
        <Text style={luxuryStyles.heroSubtitle}>Premium Sunglasses</Text>
        <Text style={luxuryStyles.heroDescription}>
          Where sophistication meets innovation
        </Text>
        
        {/* Featured Product Image */}
        <Animated.View style={[luxuryStyles.featuredProduct, { opacity: fadeAnim }]}>
          <Image
            source={{
              uri: 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=300&h=200&fit=crop',
            }}
            style={luxuryStyles.productImage}
            resizeMode="contain"
          />
        </Animated.View>
      </Animated.View>
    </View>
  );
}