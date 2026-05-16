import React, { useEffect, useRef } from 'react';
import {
  Text,
  Animated,
  TextStyle,
} from 'react-native';

interface AnimatedTextProps {
  text: string;
  style: TextStyle;
  delay?: number;
}

export default function AnimatedText({ text, style, delay = 0 }: AnimatedTextProps) {
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(20)).current;

  useEffect(() => {
    Animated.sequence([
      Animated.delay(delay),
      Animated.parallel([
        Animated.timing(fadeAnim, {
          toValue: 1,
          duration: 800,
          useNativeDriver: true,
        }),
        Animated.timing(slideAnim, {
          toValue: 0,
          duration: 600,
          useNativeDriver: true,
        }),
      ]),
    ]).start();
  }, [delay]);

  return (
    <Animated.Text
      style={[
        style,
        {
          opacity: fadeAnim,
          transform: [{ translateY: slideAnim }],
        },
      ]}
    >
      {text}
    </Animated.Text>
  );
}